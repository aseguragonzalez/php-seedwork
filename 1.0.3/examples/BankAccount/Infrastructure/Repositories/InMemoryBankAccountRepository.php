<?php

declare(strict_types=1);

namespace Examples\BankAccount\Infrastructure\Repositories;

use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\Repositories\BankAccountRepository;
use SeedWork\Domain\AggregateRoot;

/**
 * InMemory implementation of the BankAccountRepository interface.
 */
final class InMemoryBankAccountRepository implements BankAccountRepository
{
    /** @var array<string, BankAccount> */
    private array $accounts = [];

    /**
     * @param BankAccount $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot): void
    {
        $this->accounts[(string) $aggregateRoot->id] = clone $aggregateRoot;
    }

    /**
     * @return null|BankAccount
     */
    public function findById(mixed $id): ?AggregateRoot
    {
        $key = (string) $id;

        return isset($this->accounts[$key]) ? clone $this->accounts[$key] : null;
    }

    public function deleteById(mixed $id): void
    {
        unset($this->accounts[(string) $id]);
    }

    /**
     * Returns all stored aggregates (fixture only; not on domain interface).
     *
     * @return array<BankAccount>
     */
    public function findAll(): array
    {
        return array_values(array_map(fn (BankAccount $a): BankAccount => clone $a, $this->accounts));
    }
}
