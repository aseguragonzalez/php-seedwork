<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\ValueObjects;

use Seedwork\Domain\Exceptions\ValueException;
use Seedwork\Domain\ValueObject;

final readonly class Money extends ValueObject
{
    public function __construct(public int $amount, public Currency $currency)
    {
        parent::__construct();
    }

    public function equals(ValueObject $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    protected function validate(): void
    {
        if ($this->amount <= 0) {
            throw new ValueException('Amount must be greater than 0');
        }
    }
}
