<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Events;

use Seedwork\Domain\DomainEvent;
use Seedwork\Domain\EventId;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Entities\TransactionId;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final readonly class MoneyTransferredIn extends DomainEvent
{
    public function __construct(
        public BankAccountId $toAccountId,
        public BankAccountId $fromAccountId,
        public Money $amount,
        public TransactionId $transactionId,
        ?EventId $id = null,
        ?\DateTimeImmutable $createdAt = null
    ) {
        parent::__construct(
            $id ?? BankAccountEventId::fromString('evt-' . uniqid('', true)),
            'bank_account.money_transferred_in',
            '1.0',
            [
                'to_account_id' => $toAccountId->value,
                'from_account_id' => $fromAccountId->value,
                'amount' => $amount->amount,
                'currency' => $amount->currency->value,
                'transaction_id' => $transactionId->value,
            ],
            $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
    }
}
