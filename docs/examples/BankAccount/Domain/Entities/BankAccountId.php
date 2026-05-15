<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Entities;

use Examples\BankAccount\Domain\Exceptions\BankAccountException;

final readonly class BankAccountId
{
    public static function create(): self
    {
        return new self(value: 'acc-' . uniqid('', true));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function __construct(public string $value)
    {
        if (empty($this->value)) {
            throw new BankAccountException('Bank account id cannot be empty');
        }

        if (!preg_match('/^acc-[a-z0-9.-]+$/', $this->value)) {
            throw new BankAccountException('Bank account id must start with "acc-"');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
