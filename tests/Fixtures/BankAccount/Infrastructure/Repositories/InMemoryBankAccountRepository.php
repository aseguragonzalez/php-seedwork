<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Infrastructure\Repositories;

use Seedwork\Domain\AggregateRoot;
use Seedwork\Domain\EntityId;
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
     * @return BankAccount
     */
    public function getById(EntityId $id): AggregateRoot
    {
        if (!isset($this->accounts[$id->value])) {
            throw new \RuntimeException(sprintf('BankAccount not found: %s', $id->value));
        }

        return clone $this->accounts[$id->value];
    }

    /**
     * @param BankAccountId $id
     */
    public function deleteById(EntityId $id): void
    {
        unset($this->accounts[$id->value]);
    }
}
