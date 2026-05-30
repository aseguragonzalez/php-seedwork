<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Entities;

use Examples\BankAccount\Domain\Exceptions\BankAccountException;

final readonly class TransactionId
{
    private function __construct(public string $value)
    {
        if (!preg_match('/^txn-[a-z0-9.-]+$/', $this->value)) {
            throw new BankAccountException('Transaction id must start with "txn-"');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function create(): self
    {
        return new self(value: 'txn-'.uniqid('', true));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
