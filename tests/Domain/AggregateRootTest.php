<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final class AggregateRootTest extends TestCase
{
    public function testEquals(): void
    {
        $account1 = BankAccount::create();
        $account2 = BankAccount::create();
        $account3 = BankAccount::build(
            $account1->id,
            $account1->getBalance(),
            $account1->getTransactions()
        );

        $this->assertFalse($account1->equals($account2));
        $this->assertTrue($account1->equals($account3));
    }

    public function testCollectEvents(): void
    {
        $account = BankAccount::create()
            ->deposit(new Money(100, Currency::USD))
            ->withdraw(new Money(30, Currency::USD));

        $events = $account->collectEvents();

        $this->assertCount(2, $events);
        $this->assertSame('bank_account.money_deposited', $events[0]->type);
        $this->assertSame('bank_account.money_withdrawn', $events[1]->type);
    }
}
