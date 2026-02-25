<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Infrastructure\Repositories;

use SeedWork\Domain\AggregateRoot;
use SeedWork\Domain\EntityId;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;

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
        $this->accounts[$aggregateRoot->id->value] = clone $aggregateRoot;
    }

    /**
     * @param BankAccountId $id
     * @return BankAccount|null
     */
    public function findBy(EntityId $id): ?AggregateRoot
    {
        if (!isset($this->accounts[$id->value])) {
            return null;
        }

        return clone $this->accounts[$id->value];
    }

    /**
     * @param BankAccountId $id
     */
    public function deleteBy(EntityId $id): void
    {
        unset($this->accounts[$id->value]);
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
