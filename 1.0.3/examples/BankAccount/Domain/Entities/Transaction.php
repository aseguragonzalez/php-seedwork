<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Entities;

use Examples\BankAccount\Domain\Exceptions\BankAccountException;
use Examples\BankAccount\Domain\ValueObjects\Money;
use Examples\BankAccount\Domain\ValueObjects\TransactionType;
use SeedWork\Domain\Entity;

/**
 * @extends Entity<TransactionId>
 */
final readonly class Transaction extends Entity
{
    private function __construct(
        TransactionId $id,
        public TransactionType $type,
        public Money $amount,
        public \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id);
    }

    public static function create(
        TransactionType $type,
        Money $amount,
        ?TransactionId $id = null,
        ?\DateTimeImmutable $createdAt = null,
    ): self {
        return new self(
            id: $id ?? TransactionId::create(),
            type: $type,
            amount: $amount,
            createdAt: $createdAt ?? new \DateTimeImmutable(),
        );
    }

    public static function build(
        TransactionId $id,
        TransactionType $type,
        Money $amount,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $type, $amount, $createdAt);
    }

    protected function validate(): void
    {
        if ($this->amount->amount <= 0) {
            throw new BankAccountException('Transaction amount must be greater than 0');
        }

        if ($this->createdAt > new \DateTimeImmutable()) {
            throw new BankAccountException('Transaction created at cannot be in the future');
        }
    }
}
