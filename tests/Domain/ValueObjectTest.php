<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Seedwork\Domain\Exceptions\ValueException;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final class ValueObjectTest extends TestCase
{
    public function testValidationFailureThrowsValueException(): void
    {
        $this->expectException(ValueException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        new Money(-1, Currency::USD);
    }
}
