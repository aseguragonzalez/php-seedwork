<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Events;

use SeedWork\Domain\DomainEvent;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Entities\TransactionId;

final readonly class MoneyTransferredOut extends DomainEvent
{
    public function __construct(
        public BankAccountId $fromAccountId,
        public BankAccountId $toAccountId,
        public \Examples\BankAccount\Domain\ValueObjects\Money $amount,
        public TransactionId $transactionId,
        BankAccountEventId $id,
        \DateTimeImmutable $createdAt
    ) {
        parent::__construct($id, $createdAt);
    }

    public static function create(
        \Examples\BankAccount\Domain\ValueObjects\Money $amount,
        BankAccountId $fromAccountId,
        BankAccountId $toAccountId,
        TransactionId $transactionId,
        ?BankAccountEventId $id = null,
        ?\DateTimeImmutable $createdAt = null
    ): self {
        return new self(
            $fromAccountId,
            $toAccountId,
            $amount,
            $transactionId,
            $id ?? BankAccountEventId::create(),
            $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
    }
}
