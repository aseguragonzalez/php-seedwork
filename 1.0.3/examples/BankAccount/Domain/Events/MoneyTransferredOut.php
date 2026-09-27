<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Events;

use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Entities\TransactionId;
use Examples\BankAccount\Domain\ValueObjects\Money;
use SeedWork\Domain\DomainEvent;

final readonly class MoneyTransferredOut extends DomainEvent
{
    private function __construct(
        public BankAccountId $fromAccountId,
        public BankAccountId $toAccountId,
        public Money $amount,
        public TransactionId $transactionId,
        string $id,
        \DateTimeImmutable $occurredAt
    ) {
        parent::__construct($id, (string) $fromAccountId, $occurredAt);
    }

    public static function create(
        Money $amount,
        BankAccountId $fromAccountId,
        BankAccountId $toAccountId,
        TransactionId $transactionId,
        ?string $id = null,
        ?\DateTimeImmutable $occurredAt = null
    ): self {
        return new self(
            $fromAccountId,
            $toAccountId,
            $amount,
            $transactionId,
            $id ?? 'evt-'.uniqid('', true),
            $occurredAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
    }
}
