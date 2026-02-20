<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Entities;

use Seedwork\Domain\EntityId;
use Seedwork\Domain\Exceptions\ValueException;

final readonly class TransactionId extends EntityId
{
    public static function create(): self
    {
        return new self(value: 'txn-' . uniqid('', true));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function __construct(string $value)
    {
        parent::__construct($value);
    }

    protected function validate(): void
    {
        if (!preg_match('/^txn-[a-z0-9.-]+$/', $this->value)) {
            throw new ValueException('Transaction id must start with "txn-"');
        }
    }
}
