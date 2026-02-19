<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Entities;

use Seedwork\Domain\EntityId;

final readonly class TransactionId extends EntityId
{
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(): self
    {
        return new self('txn-' . uniqid('', true));
    }

    private function __construct(string $value)
    {
        parent::__construct($value);
    }
}
