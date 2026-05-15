<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Events;

use SeedWork\Domain\DomainEvent;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Entities\TransactionId;
use Examples\BankAccount\Domain\ValueObjects\Money;

final readonly class MoneyWithdrawn extends DomainEvent
{
    private function __construct(
        public BankAccountId $accountId,
        public Money $amount,
        public TransactionId $transactionId,
        string $id,
        \DateTimeImmutable $createdAt
    ) {
        parent::__construct($id, $createdAt);
    }

    public static function create(
        Money $amount,
        BankAccountId $accountId,
        TransactionId $transactionId,
        ?string $id = null,
        ?\DateTimeImmutable $createdAt = null
    ): self {
        return new self(
            $accountId,
            $amount,
            $transactionId,
            $id ?? 'evt-' . uniqid('', true),
            $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
    }
}
