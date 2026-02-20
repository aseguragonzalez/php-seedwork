<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Events;

use Seedwork\Domain\DomainEvent;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Entities\TransactionId;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final readonly class MoneyTransferredOut extends DomainEvent
{
    public function __construct(
        public BankAccountId $fromAccountId,
        public BankAccountId $toAccountId,
        public Money $amount,
        public TransactionId $transactionId,
        BankAccountEventId $id,
        \DateTimeImmutable $createdAt
    ) {
        parent::__construct(
            $id,
            'bank_account.money_transferred_out',
            '1.0',
            [
                'from_account_id' => $fromAccountId->value,
                'to_account_id' => $toAccountId->value,
                'amount' => $amount->amount,
                'currency' => $amount->currency->value,
                'transaction_id' => $transactionId->value,
            ],
            $createdAt
        );
    }

    public static function create(
        Money $amount,
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
