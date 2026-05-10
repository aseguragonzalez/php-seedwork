<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Events;

use SeedWork\Domain\DomainEvent;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Entities\TransactionId;

final readonly class MoneyTransferredIn extends DomainEvent
{
    private function __construct(
        public BankAccountId $toAccountId,
        public BankAccountId $fromAccountId,
        public \Examples\BankAccount\Domain\ValueObjects\Money $amount,
        public TransactionId $transactionId,
        BankAccountEventId $id,
        \DateTimeImmutable $createdAt
    ) {
        parent::__construct($id, $createdAt);
    }

    public static function create(
        \Examples\BankAccount\Domain\ValueObjects\Money $amount,
        BankAccountId $toAccountId,
        BankAccountId $fromAccountId,
        TransactionId $transactionId,
        ?BankAccountEventId $id = null,
        ?\DateTimeImmutable $createdAt = null
    ): self {
        return new self(
            $toAccountId,
            $fromAccountId,
            $amount,
            $transactionId,
            $id ?? BankAccountEventId::create(),
            $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
    }
}
