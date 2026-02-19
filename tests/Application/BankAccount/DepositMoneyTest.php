<?php

declare(strict_types=1);

namespace Tests\Application\BankAccount;

use PHPUnit\Framework\TestCase;
use Seedwork\Application\DomainEventsBus;
use Tests\Fixtures\BankAccount\Application\DepositMoney\DepositMoneyCommand;
use Tests\Fixtures\BankAccount\Application\DepositMoney\DepositMoneyCommandHandler;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyDeposited;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final class DepositMoneyTest extends TestCase
{
    public function testDepositIncreasesBalance(): void
    {
        $accountId = BankAccountId::fromString('acc-001');
        $account = BankAccount::create($accountId)->deposit(new Money(100, Currency::USD));

        $repository = $this->createMock(BankAccountRepository::class);
        $repository->method('getById')->willReturn($account);
        /** @var BankAccount|null $savedAccount */
        $savedAccount = null;
        $repository->expects($this->once())->method('save')->willReturnCallback(
            function ($arg) use (&$savedAccount) {
                $savedAccount = $arg;
            }
        );
        $domainEventsBus = $this->createStub(DomainEventsBus::class);
        $handler = new DepositMoneyCommandHandler($repository, $domainEventsBus);

        $handler->handle(new DepositMoneyCommand($accountId, new Money(50, Currency::USD)));

        $this->assertNotNull($savedAccount);
        /** @var BankAccount $savedAccount */
        $this->assertEquals(150, $savedAccount->getBalance()->amount);
    }

    public function testDepositPublishesMoneyDepositedEvent(): void
    {
        $accountId = BankAccountId::fromString('acc-001');
        $account = BankAccount::create($accountId);

        $repository = $this->createStub(BankAccountRepository::class);
        $repository->method('getById')->willReturn($account);

        $publishedEvents = [];
        $domainEventsBus = $this->createMock(DomainEventsBus::class);
        $domainEventsBus->expects($this->atLeastOnce())->method('publish')->willReturnCallback(
            function ($event) use (&$publishedEvents) {
                $publishedEvents[] = $event;
            }
        );

        $handler = new DepositMoneyCommandHandler($repository, $domainEventsBus);
        $handler->handle(new DepositMoneyCommand($accountId, new Money(75, Currency::USD)));

        $depositedEvents = array_filter($publishedEvents, fn ($e) => $e instanceof MoneyDeposited);
        $this->assertCount(1, $depositedEvents);
        $this->assertSame('acc-001', $depositedEvents[0]->accountId->value);
        $this->assertEquals(75, $depositedEvents[0]->amount->amount);
    }
}
