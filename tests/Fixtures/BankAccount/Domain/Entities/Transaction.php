<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Entities;

use SeedWork\Domain\Entity;
use SeedWork\Domain\Exceptions\ValueException;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\TransactionType;

/**
 * @extends Entity<TransactionId>
 */
final readonly class Transaction extends Entity
{
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

    private function __construct(
        TransactionId $id,
        public TransactionType $type,
        public Money $amount,
        public \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id);
    }

    protected function validate(): void
    {
        if ($this->amount->amount <= 0) {
            throw new ValueException('Transaction amount must be greater than 0');
        }

        if ($this->createdAt > new \DateTimeImmutable()) {
            throw new ValueException('Transaction created at cannot be in the future');
        }
    }
}
