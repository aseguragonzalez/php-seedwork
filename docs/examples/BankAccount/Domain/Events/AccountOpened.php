<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Events;

use SeedWork\Domain\DomainEvent;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;

final readonly class AccountOpened extends DomainEvent
{
    private function __construct(
        public BankAccountId $accountId,
        public AccountBalance $initialBalance,
        string $id,
        \DateTimeImmutable $createdAt
    ) {
        parent::__construct($id, $createdAt);
    }

    public static function create(
        BankAccountId $accountId,
        AccountBalance $initialBalance,
        ?string $id = null,
        ?\DateTimeImmutable $createdAt = null
    ): self {
        return new self(
            $accountId,
            $initialBalance,
            $id ?? 'evt-' . uniqid('', true),
            $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
    }
}
