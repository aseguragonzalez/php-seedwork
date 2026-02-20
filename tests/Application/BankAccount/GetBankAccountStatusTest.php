<?php

declare(strict_types=1);

namespace Tests\Application\BankAccount;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\BankAccount\Application\GetBankAccountStatus\BankAccountStatusResult;
use Tests\Fixtures\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQuery;
use Tests\Fixtures\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQueryHandler;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\AccountBalance;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\TransactionType;

final class GetBankAccountStatusTest extends TestCase
{
    public function testReturnsAccountDetailsAndBalance(): void
    {
        $accountId = BankAccountId::fromString('acc-001');
        $account = BankAccount::create($accountId)->deposit(new Money(100, Currency::USD));

        $repository = $this->createStub(BankAccountRepository::class);
        $repository->method('findBy')->willReturn($account);

        $handler = new GetBankAccountStatusQueryHandler($repository);

        /** @var BankAccountStatusResult $result */
        $result = $handler->handle(new GetBankAccountStatusQuery($accountId));

        $this->assertInstanceOf(BankAccountStatusResult::class, $result);
        $this->assertSame('acc-001', $result->accountId->value);
        $this->assertEquals(100, $result->balance->amount);
        $this->assertSame(Currency::USD, $result->balance->currency);
    }

    public function testReturnsTransactionsList(): void
    {
        $accountId = BankAccountId::fromString('acc-001');
        $account = BankAccount::create($accountId)
            ->deposit(new Money(100, Currency::USD))
            ->deposit(new Money(50, Currency::USD));

        $repository = $this->createStub(BankAccountRepository::class);
        $repository->method('findBy')->willReturn($account);

        $handler = new GetBankAccountStatusQueryHandler($repository);

        /** @var BankAccountStatusResult $result */
        $result = $handler->handle(new GetBankAccountStatusQuery($accountId));

        $this->assertCount(2, $result->transactions);
        $this->assertSame(TransactionType::DEPOSIT, $result->transactions[0]->type);
        $this->assertEquals(100, $result->transactions[0]->amount);
        $this->assertSame('USD', $result->transactions[0]->currency);
        $this->assertSame(TransactionType::DEPOSIT, $result->transactions[1]->type);
        $this->assertEquals(50, $result->transactions[1]->amount);
    }

    public function testReturnsFullHistoryAfterWithdrawAndTransfer(): void
    {
        $fromId = BankAccountId::fromString('acc-from');
        $toId = BankAccountId::fromString('acc-to');

        $fromAccount = BankAccount::create(
            $fromId,
            new AccountBalance(0, Currency::EUR)
        )
            ->deposit(new Money(200, Currency::EUR))
            ->withdraw(new Money(50, Currency::EUR))
            ->transferOut(new Money(30, Currency::EUR), $toId);

        $repository = $this->createStub(BankAccountRepository::class);
        $repository->method('findBy')->willReturn($fromAccount);

        $handler = new GetBankAccountStatusQueryHandler($repository);

        /** @var BankAccountStatusResult $result */
        $result = $handler->handle(new GetBankAccountStatusQuery($fromId));

        $this->assertEquals(120, $result->balance->amount);
        $this->assertCount(3, $result->transactions);

        $types = array_map(fn ($t) => $t->type, $result->transactions);
        $this->assertContains(TransactionType::DEPOSIT, $types);
        $this->assertContains(TransactionType::WITHDRAWAL, $types);
        $this->assertContains(TransactionType::TRANSFER_OUT, $types);
    }
}
