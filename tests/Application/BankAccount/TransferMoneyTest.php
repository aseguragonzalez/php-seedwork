<?php

declare(strict_types=1);

namespace Tests\Application\BankAccount;

use PHPUnit\Framework\TestCase;
use Seedwork\Application\DomainEventsBus;
use Seedwork\Domain\DomainEvent;
use Tests\Fixtures\BankAccount\Application\TransferMoney\TransferMoneyCommand;
use Tests\Fixtures\BankAccount\Application\TransferMoney\TransferMoneyCommandHandler;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyTransferredIn;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyTransferredOut;
use Tests\Fixtures\BankAccount\Domain\Exceptions\InsufficientFundsException;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final class TransferMoneyTest extends TestCase
{
    public function testTransferDebitsSourceAndCreditsTarget(): void
    {
        $fromId = BankAccountId::fromString('acc-from');
        $toId = BankAccountId::fromString('acc-to');
        $fromAccount = BankAccount::create($fromId)->deposit(new Money(100, Currency::USD));
        $toAccount = BankAccount::create($toId);
        $repository = $this->createMock(BankAccountRepository::class);
        $repository->method('getById')->willReturnCallback(
            fn (BankAccountId $id) => $id->value === 'acc-from' ? $fromAccount : $toAccount
        );
        /** @var array<BankAccount> $savedAccounts */
        $savedAccounts = [];
        $repository->expects($this->exactly(2))->method('save')->willReturnCallback(
            function (BankAccount $account) use (&$savedAccounts) {
                $savedAccounts[] = $account;
            }
        );
        $domainEventsBus = $this->createStub(DomainEventsBus::class);
        $handler = new TransferMoneyCommandHandler($repository, $domainEventsBus);

        $handler->handle(new TransferMoneyCommand($fromId, $toId, new Money(40, Currency::USD)));

        $this->assertCount(2, $savedAccounts);
        $balances = array_map(fn (BankAccount $a) => $a->getBalance()->amount, $savedAccounts);
        $this->assertContains(60, $balances);
        $this->assertContains(40, $balances);
    }

    public function testTransferPublishesMoneyTransferredOutAndInEvents(): void
    {
        $fromId = BankAccountId::fromString('acc-from');
        $toId = BankAccountId::fromString('acc-to');
        $fromAccount = BankAccount::create($fromId)->deposit(new Money(100, Currency::USD));
        $toAccount = BankAccount::create($toId);

        $repository = $this->createStub(BankAccountRepository::class);
        $repository->method('getById')->willReturnCallback(
            fn (BankAccountId $id) => $id->value === 'acc-from' ? $fromAccount : $toAccount
        );
        /** @var array<MoneyTransferredOut|MoneyTransferredIn> $publishedEvents */
        $publishedEvents = [];
        $domainEventsBus = $this->createMock(DomainEventsBus::class);
        $domainEventsBus->expects($this->atLeastOnce())->method('publish')->willReturnCallback(
            function ($event) use (&$publishedEvents) {
                $publishedEvents[] = $event;
            }
        );

        $handler = new TransferMoneyCommandHandler($repository, $domainEventsBus);
        $handler->handle(new TransferMoneyCommand($fromId, $toId, new Money(40, Currency::USD)));

        $outEvents = array_values(array_filter($publishedEvents, fn ($e) => $e instanceof MoneyTransferredOut));
        $inEvents = array_values(array_filter($publishedEvents, fn ($e) => $e instanceof MoneyTransferredIn));
        $this->assertCount(1, $outEvents);
        $this->assertCount(1, $inEvents);
        $this->assertInstanceOf(MoneyTransferredOut::class, $outEvents[0]);
        $this->assertInstanceOf(MoneyTransferredIn::class, $inEvents[0]);
        $this->assertSame('acc-from', $outEvents[0]->fromAccountId->value);
        $this->assertSame('acc-to', $outEvents[0]->toAccountId->value);
        $this->assertEquals(40, $outEvents[0]->amount->amount);
        $this->assertSame('acc-to', $inEvents[0]->toAccountId->value);
        $this->assertSame('acc-from', $inEvents[0]->fromAccountId->value);
    }

    public function testTransferInsufficientFundsThrows(): void
    {
        $fromId = BankAccountId::fromString('acc-from');
        $toId = BankAccountId::fromString('acc-to');
        $fromAccount = BankAccount::create($fromId)->deposit(new Money(30, Currency::USD));
        $toAccount = BankAccount::create($toId);

        $repository = $this->createStub(BankAccountRepository::class);
        $repository->method('getById')->willReturnCallback(
            fn (BankAccountId $id) => $id->value === 'acc-from' ? $fromAccount : $toAccount
        );
        $domainEventsBus = $this->createStub(DomainEventsBus::class);

        $handler = new TransferMoneyCommandHandler($repository, $domainEventsBus);

        $this->expectException(InsufficientFundsException::class);

        $handler->handle(new TransferMoneyCommand($fromId, $toId, new Money(50, Currency::USD)));
    }
}
