<?php

declare(strict_types=1);

namespace Examples\BankAccount\Tests;

use PHPUnit\Framework\TestCase;
use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\Events\AccountOpened;
use Examples\BankAccount\Domain\Events\MoneyDeposited;
use Examples\BankAccount\Domain\Events\MoneyWithdrawn;
use Examples\BankAccount\Domain\Exceptions\InsufficientFundsException;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Domain\ValueObjects\Money;

final class BankAccountTest extends TestCase
{
    public function testCreateOpensBankAccountWithZeroBalance(): void
    {
        $account = BankAccount::create();

        $this->assertSame(0, $account->getBalance()->amount);
        $this->assertSame(Currency::USD, $account->getBalance()->currency);
    }

    public function testCreateWithInitialBalanceSetsBalance(): void
    {
        $account = BankAccount::create(initialBalance: new AccountBalance(500, Currency::EUR));

        $this->assertSame(500, $account->getBalance()->amount);
        $this->assertSame(Currency::EUR, $account->getBalance()->currency);
    }

    public function testCreateRaisesAccountOpenedEvent(): void
    {
        $account = BankAccount::create(initialBalance: new AccountBalance(100, Currency::USD));

        $events = $account->getDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(AccountOpened::class, $events[0]);
        $this->assertSame((string) $account->id, $events[0]->aggregateId);
    }

    public function testDepositIncreasesBalance(): void
    {
        $account = BankAccount::create(initialBalance: new AccountBalance(100, Currency::USD));

        $updated = $account->deposit(new Money(50, Currency::USD));

        $this->assertSame(150, $updated->getBalance()->amount);
    }

    public function testDepositRaisesMoneyDepositedEvent(): void
    {
        $account = BankAccount::create(initialBalance: new AccountBalance(100, Currency::USD));

        $updated = $account->deposit(new Money(50, Currency::USD));

        $events = $updated->getDomainEvents();
        $this->assertCount(2, $events);
        $this->assertInstanceOf(MoneyDeposited::class, $events[1]);
    }

    public function testWithdrawDecreasesBalance(): void
    {
        $account = BankAccount::create(initialBalance: new AccountBalance(100, Currency::USD));

        $updated = $account->withdraw(new Money(30, Currency::USD));

        $this->assertSame(70, $updated->getBalance()->amount);
    }

    public function testWithdrawRaisesMoneyWithdrawnEvent(): void
    {
        $account = BankAccount::create(initialBalance: new AccountBalance(100, Currency::USD));

        $updated = $account->withdraw(new Money(30, Currency::USD));

        $events = $updated->getDomainEvents();
        $this->assertCount(2, $events);
        $this->assertInstanceOf(MoneyWithdrawn::class, $events[1]);
    }

    public function testWithdrawThrowsWhenInsufficientFunds(): void
    {
        $account = BankAccount::create(initialBalance: new AccountBalance(50, Currency::USD));

        $this->expectException(InsufficientFundsException::class);
        $account->withdraw(new Money(100, Currency::USD));
    }

    public function testDepositWithMismatchedCurrencyThrows(): void
    {
        $account = BankAccount::create(initialBalance: new AccountBalance(100, Currency::USD));

        $this->expectException(\InvalidArgumentException::class);
        $account->deposit(new Money(50, Currency::EUR));
    }

    public function testWithdrawWithMismatchedCurrencyThrows(): void
    {
        $account = BankAccount::create(initialBalance: new AccountBalance(100, Currency::USD));

        $this->expectException(\InvalidArgumentException::class);
        $account->withdraw(new Money(30, Currency::EUR));
    }
}
