<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use SeedWork\Domain\Exceptions\ValueException;
use Tests\Fixtures\BankAccount\Domain\Entities\Transaction;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\TransactionType;

final class EntityTest extends TestCase
{
    public function testEquals(): void
    {
        $type = TransactionType::DEPOSIT;
        $amount = new Money(100, Currency::USD);
        $transaction = Transaction::create($type, $amount);
        $transaction2 = Transaction::build($transaction->id, $type, $amount, $transaction->createdAt);
        $transaction3 = Transaction::create($type, $amount);

        $this->assertTrue($transaction->equals($transaction2));
        $this->assertFalse($transaction->equals($transaction3));
    }

    public function testValidate(): void
    {
        $this->expectException(ValueException::class);
        $this->expectExceptionMessage('Transaction created at cannot be in the future');

        Transaction::create(
            TransactionType::DEPOSIT,
            new Money(100, Currency::USD),
            createdAt: new \DateTimeImmutable('tomorrow'),
        );
    }
}
