<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Events;

use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;
use SeedWork\Domain\DomainEvent;

final readonly class AccountOpened extends DomainEvent
{
    private function __construct(
        public BankAccountId $accountId,
        public AccountBalance $initialBalance,
        string $id,
        \DateTimeImmutable $occurredAt
    ) {
        parent::__construct($id, (string) $accountId, $occurredAt);
    }

    public static function create(
        BankAccountId $accountId,
        AccountBalance $initialBalance,
        ?string $id = null,
        ?\DateTimeImmutable $occurredAt = null
    ): self {
        return new self(
            $accountId,
            $initialBalance,
            $id ?? 'evt-'.uniqid('', true),
            $occurredAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
    }
}
