<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Events;

use SeedWork\Domain\EventId;
use Examples\BankAccount\Domain\Exceptions\BankAccountException;

final readonly class BankAccountEventId extends EventId
{
    public static function create(): self
    {
        return new self(value: 'evt-' . uniqid('', true));
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
            throw new BankAccountException('Event id cannot be empty');
        }

        if (!preg_match('/^evt-[a-z0-9.-]+$/', $this->value)) {
            throw new BankAccountException('Event id must start with "evt-"');
        }
    }
}
