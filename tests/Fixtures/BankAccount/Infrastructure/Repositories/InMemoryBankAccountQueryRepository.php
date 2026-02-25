<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Infrastructure\Repositories;

use SeedWork\Application\FilterCriteria;
use SeedWork\Application\FilterOperator;
use Tests\Fixtures\BankAccount\Application\GetBankAccountStatus\BankAccountProjection;
use Tests\Fixtures\BankAccount\Application\GetBankAccountStatus\BankAccountQueryRepository;
use Tests\Fixtures\BankAccount\Application\GetBankAccountStatus\TransactionProjection;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Entities\Transaction;

/**
 * In-memory QueryRepository that derives projections from the aggregate store.
 */
final class InMemoryBankAccountQueryRepository implements BankAccountQueryRepository
{
    public function __construct(
        private readonly InMemoryBankAccountRepository $aggregateRepository,
    ) {
    }

    public function getById(string $id): ?object
    {
        $account = $this->aggregateRepository->findBy(BankAccountId::fromString($id));

        return $account !== null ? $this->mapToProjection($account) : null;
    }

    /**
     * @param array<FilterCriteria<mixed>> $filters
     *
     * @return array<BankAccountProjection>
     */
    public function filter(int $offset, int $limit, array $filters): array
    {
        $all = $this->getAllProjections();
        $filtered = $this->applyFilters($all, $filters);

        return array_slice($filtered, $offset, $limit);
    }

    /**
     * @return array<BankAccountProjection>
     */
    private function getAllProjections(): array
    {
        $accounts = $this->aggregateRepository->findAll();

        return array_map($this->mapToProjection(...), $accounts);
    }

    private function mapToProjection(BankAccount $account): BankAccountProjection
    {
        $balance = $account->getBalance();
        $transactions = array_map(
            fn (Transaction $t) => new TransactionProjection(
                $t->id->value,
                $t->type->value,
                $t->amount->amount,
                $t->amount->currency->value,
                $t->createdAt->format(\DateTimeInterface::ATOM),
            ),
            $account->getTransactions()
        );

        return new BankAccountProjection(
            $account->id->value,
            $balance->amount,
            $balance->currency->value,
            $transactions,
        );
    }

    /**
     * @param array<BankAccountProjection> $projections
     * @param array<FilterCriteria<mixed>> $filters
     *
     * @return array<BankAccountProjection>
     */
    private function applyFilters(array $projections, array $filters): array
    {
        if ($filters === []) {
            return $projections;
        }

        return array_values(array_filter(
            $projections,
            fn (BankAccountProjection $p) => $this->matchesAllFilters($p, $filters)
        ));
    }

    /**
     * @param array<FilterCriteria<mixed>> $filters
     */
    private function matchesAllFilters(BankAccountProjection $projection, array $filters): bool
    {
        foreach ($filters as $criteria) {
            if (!$this->matchesFilter($projection, $criteria)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param FilterCriteria<mixed> $criteria
     */
    private function matchesFilter(BankAccountProjection $projection, FilterCriteria $criteria): bool
    {
        $value = match ($criteria->field) {
            'id' => $projection->id,
            'balanceAmount', 'balance' => $projection->balanceAmount,
            'currency' => $projection->currency,
            default => null,
        };

        if ($value === null) {
            return false;
        }

        return $this->compare($value, $criteria->operator, $criteria->value);
    }

    private function compare(mixed $actual, FilterOperator $operator, mixed $expected): bool
    {
        return match ($operator) {
            FilterOperator::EQ => $actual == $expected,
            FilterOperator::NEQ => $actual != $expected,
            FilterOperator::GT => $actual > $expected,
            FilterOperator::GTE => $actual >= $expected,
            FilterOperator::LT => $actual < $expected,
            FilterOperator::LTE => $actual <= $expected,
            FilterOperator::IN => is_array($expected) && in_array($actual, $expected, true),
            FilterOperator::BETWEEN => is_array($expected)
                && count($expected) >= 2
                && $actual >= $expected[0]
                && $actual <= $expected[1],
            FilterOperator::LIKE => is_string($actual) && is_string($expected)
                && str_contains(strtolower($actual), strtolower($expected)),
        };
    }
}
