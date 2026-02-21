<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Events;

use SeedWork\Domain\DomainEvent;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Entities\TransactionId;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final readonly class MoneyDeposited extends DomainEvent
{
    private function __construct(
        public BankAccountId $accountId,
        public Money $amount,
        public TransactionId $transactionId,
        BankAccountEventId $id,
        \DateTimeImmutable $createdAt
    ) {
        parent::__construct(
            $id,
            'bank_account.money_deposited',
            '1.0',
            [
                'account_id' => $accountId->value,
                'amount' => $amount->amount,
                'currency' => $amount->currency->value,
                'transaction_id' => $transactionId->value,
            ],
            $createdAt
        );
    }

    public static function create(
        Money $amount,
        BankAccountId $accountId,
        TransactionId $transactionId,
        ?BankAccountEventId $id = null,
        ?\DateTimeImmutable $createdAt = null
    ): self {
        return new self(
            $accountId,
            $amount,
            $transactionId,
            $id ?? BankAccountEventId::create(),
            $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
    }
}
