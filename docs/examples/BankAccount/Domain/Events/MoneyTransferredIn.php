<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Events;

use SeedWork\Domain\DomainEvent;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Entities\TransactionId;
use Examples\BankAccount\Domain\Events\BankAccountEventId;
use Examples\BankAccount\Domain\ValueObjects\Money;

final readonly class MoneyTransferredIn extends DomainEvent
{
    private function __construct(
        public BankAccountId $toAccountId,
        public BankAccountId $fromAccountId,
        public Money $amount,
        public TransactionId $transactionId,
        BankAccountEventId $id,
        \DateTimeImmutable $createdAt
    ) {
        parent::__construct(
            $id,
            'bank_account.money_transferred_in',
            '1.0',
            [
                'to_account_id' => $toAccountId->value,
                'from_account_id' => $fromAccountId->value,
                'amount' => $amount->amount,
                'currency' => $amount->currency->value,
                'transaction_id' => $transactionId->value,
            ],
            $createdAt
        );
    }

    public static function create(
        Money $amount,
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
