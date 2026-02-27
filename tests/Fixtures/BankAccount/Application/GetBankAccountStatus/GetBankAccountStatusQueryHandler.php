<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\GetBankAccountStatus;

use SeedWork\Application\Query;
use SeedWork\Application\QueryResult;
use SeedWork\Domain\Exceptions\NotFoundResource;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\AccountBalance;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\TransactionType;

/**
 * Handler for the GetBankAccountStatus query.
 */
final readonly class GetBankAccountStatusQueryHandler implements GetBankAccountStatus
{
    public function __construct(
        private BankAccountQueryRepository $queryRepository,
    ) {
    }

    /**
     * @param GetBankAccountStatusQuery $query
     * @return BankAccountStatusResult
     */
    public function handle(Query $query): QueryResult
    {
        $accountId = BankAccountId::fromString($query->accountId);
        $projection = $this->queryRepository->getById($accountId->value);

        if ($projection === null) {
            throw new NotFoundResource('BankAccount', $accountId);
        }

        /** @var BankAccountProjection $projection */
        $transactions = array_map(
            fn (TransactionProjection $t) => new TransactionDto(
                $t->id,
                TransactionType::from($t->type),
                $t->amount,
                $t->currency,
                $t->createdAt,
            ),
            $projection->transactions
        );

        return new BankAccountStatusResult(
            BankAccountId::fromString($projection->id),
            new AccountBalance($projection->balanceAmount, Currency::from($projection->currency)),
            $transactions
        );
    }
}
