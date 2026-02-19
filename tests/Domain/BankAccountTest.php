<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Exceptions\InsufficientFundsException;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\AccountBalance;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\TransactionType;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final class BankAccountTest extends TestCase
{
    public function testDepositIncreasesBalance(): void
    {
        $account = BankAccount::create(BankAccountId::fromString('acc-001'))
            ->deposit(new Money(100, Currency::USD));

        $this->assertEquals(100, $account->getBalance()->amount);
        $this->assertSame(Currency::USD, $account->getBalance()->currency);
    }

    public function testMultipleDepositsAccumulate(): void
    {
        $account = BankAccount::create(BankAccountId::fromString('acc-001'))
            ->deposit(new Money(100, Currency::USD))
            ->deposit(new Money(50, Currency::USD));

        $this->assertEquals(150, $account->getBalance()->amount);
    }

    public function testWithdrawDecreasesBalance(): void
    {
        $account = BankAccount::create(BankAccountId::fromString('acc-001'))
            ->deposit(new Money(100, Currency::USD))
            ->withdraw(new Money(30, Currency::USD));

        $this->assertEquals(70, $account->getBalance()->amount);
    }

    public function testWithdrawRecordsTransaction(): void
    {
        $account = BankAccount::create(BankAccountId::fromString('acc-001'))
            ->deposit(new Money(100, Currency::USD))
            ->withdraw(new Money(30, Currency::USD));

        $transactions = $account->getTransactions();

        $this->assertCount(2, $transactions);
        $this->assertSame(TransactionType::DEPOSIT, $transactions[0]->type);
        $this->assertSame(TransactionType::WITHDRAWAL, $transactions[1]->type);
        $this->assertEquals(30, $transactions[1]->amount->amount);
    }

    public function testInsufficientFundsThrows(): void
    {
        $account = BankAccount::create(BankAccountId::fromString('acc-001'))
            ->deposit(new Money(50, Currency::USD));

        $this->expectException(InsufficientFundsException::class);
        $this->expectExceptionMessage('Insufficient funds: balance 50, requested 100');

        $account->withdraw(new Money(100, Currency::USD));
    }

    public function testTransferOutDecreasesBalance(): void
    {
        $fromId = BankAccountId::fromString('acc-from');
        $toId = BankAccountId::fromString('acc-to');

        $account = BankAccount::create($fromId)
            ->deposit(new Money(100, Currency::USD))
            ->transferOut(new Money(40, Currency::USD), $toId);

        $this->assertEquals(60, $account->getBalance()->amount);
    }

    public function testTransferInIncreasesBalance(): void
    {
        $fromId = BankAccountId::fromString('acc-from');
        $toId = BankAccountId::fromString('acc-to');

        $account = BankAccount::create($toId)
            ->transferIn(new Money(40, Currency::USD), $fromId);

        $this->assertEquals(40, $account->getBalance()->amount);
    }

    public function testTransferOutRecordsRelatedAccount(): void
    {
        $fromId = BankAccountId::fromString('acc-from');
        $toId = BankAccountId::fromString('acc-to');

        $account = BankAccount::create($fromId)
            ->deposit(new Money(100, Currency::USD))
            ->transferOut(new Money(40, Currency::USD), $toId);

        $transactions = $account->getTransactions();

        $this->assertCount(2, $transactions);
        $this->assertSame(TransactionType::TRANSFER_OUT, $transactions[1]->type);
        $this->assertTrue($transactions[1]->relatedAccountId?->equals($toId));
    }

    public function testCreateWithInitialBalance(): void
    {
        $account = BankAccount::create(
            BankAccountId::fromString('acc-001'),
            new AccountBalance(500, Currency::EUR)
        );

        $this->assertEquals(500, $account->getBalance()->amount);
        $this->assertSame(Currency::EUR, $account->getBalance()->currency);
    }

    public function testCurrencyMismatchThrows(): void
    {
        $account = BankAccount::create(BankAccountId::fromString('acc-001'))
            ->deposit(new Money(100, Currency::USD));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency mismatch');

        $account->deposit(new Money(50, Currency::EUR));
    }
}
