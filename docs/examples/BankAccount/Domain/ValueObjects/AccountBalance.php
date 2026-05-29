<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\ValueObjects;

use Examples\BankAccount\Domain\Exceptions\BankAccountException;
use SeedWork\Domain\ValueObject;

final readonly class AccountBalance extends ValueObject
{
    public function __construct(
        public int $amount,
        public Currency $currency
    ) {
        parent::__construct();
    }

    public function equals(ValueObject $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public static function zero(Currency $currency = Currency::USD): self
    {
        return new self(0, $currency);
    }

    protected function validate(): void
    {
        if ($this->amount < 0) {
            throw new BankAccountException('Balance cannot be negative');
        }
    }
}
