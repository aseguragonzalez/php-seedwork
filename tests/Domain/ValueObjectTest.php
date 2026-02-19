<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Seedwork\Domain\Exceptions\ValueException;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final class ValueObjectTest extends TestCase
{
    public function testValidationRunsOnConstruction(): void
    {
        $money = new Money(100, Currency::USD);

        $this->assertSame(100, $money->amount);
        $this->assertSame(Currency::USD, $money->currency);
    }

    public function testValidationFailureThrowsValueException(): void
    {
        $this->expectException(ValueException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        new Money(-1, Currency::USD);
    }

    public function testEqualsReflexivity(): void
    {
        $a = new Money(10, Currency::USD);

        $this->assertTrue($a->equals($a));
    }

    public function testEqualsSameValueReturnsTrue(): void
    {
        $a = new Money(100, Currency::GBP);
        $b = new Money(100, Currency::GBP);

        $this->assertTrue($a->equals($b));
    }

    public function testEqualsDifferentValueReturnsFalse(): void
    {
        $a = new Money(100, Currency::USD);
        $b = new Money(200, Currency::USD);
        $c = new Money(100, Currency::EUR);

        $this->assertFalse($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function testEqualsSymmetry(): void
    {
        $a = new Money(5, Currency::EUR);
        $b = new Money(5, Currency::EUR);
        $c = new Money(10, Currency::EUR);

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
        $this->assertFalse($a->equals($c));
        $this->assertFalse($c->equals($a));
    }

    public function testMoneyEqualsWhenAmountAndCurrencyMatch(): void
    {
        $a = new Money(100, Currency::GBP);
        $b = new Money(100, Currency::GBP);

        $this->assertTrue($a->equals($b));
    }

    public function testMoneyNotEqualWhenAmountDiffers(): void
    {
        $a = new Money(100, Currency::USD);
        $b = new Money(200, Currency::USD);

        $this->assertFalse($a->equals($b));
    }

    public function testMoneyNotEqualWhenCurrencyDiffers(): void
    {
        $a = new Money(100, Currency::USD);
        $b = new Money(100, Currency::EUR);

        $this->assertFalse($a->equals($b));
    }

    public function testMoneyNegativeAmountThrowsValueException(): void
    {
        $this->expectException(ValueException::class);

        new Money(-50, Currency::JPY);
    }

    public function testMoneyZeroAmountThrowsValueException(): void
    {
        $this->expectException(ValueException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        new Money(0, Currency::USD);
    }
}
