<?php

declare(strict_types=1);

namespace Examples\BankAccount\Infrastructure\Repositories;

use SeedWork\Domain\AggregateRoot;
use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\Repositories\BankAccountRepository;

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
     * @param mixed $id
     * @return BankAccount|null
     */
    public function findBy(mixed $id): ?AggregateRoot
    {
        $key = (string) $id;

        return isset($this->accounts[$key]) ? clone $this->accounts[$key] : null;
    }

    /**
     * @param mixed $id
     */
    public function deleteBy(mixed $id): void
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
