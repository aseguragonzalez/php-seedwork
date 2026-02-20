<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Entities;

use Seedwork\Domain\EntityId;
use Seedwork\Domain\Exceptions\ValueException;

final readonly class BankAccountId extends EntityId
{
    public static function create(): self
    {
        return new self(value: 'acc-' . uniqid('', true));
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
        if (empty($this->value)) {
            throw new ValueException('Bank account id cannot be empty');
        }

        if (!preg_match('/^acc-[a-z0-9.-]+$/', $this->value)) {
            throw new ValueException('Bank account id must start with "acc-"');
        }
    }
}
