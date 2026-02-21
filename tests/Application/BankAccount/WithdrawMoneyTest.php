<?php

declare(strict_types=1);

namespace Tests\Application\BankAccount;

use PHPUnit\Framework\TestCase;
use Seedwork\Application\DomainEventBus;
use Tests\Fixtures\BankAccount\Application\WithdrawMoney\WithdrawMoneyCommand;
use Tests\Fixtures\BankAccount\Application\WithdrawMoney\WithdrawMoneyCommandHandler;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyWithdrawn;
use Tests\Fixtures\BankAccount\Domain\Exceptions\InsufficientFundsException;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final class WithdrawMoneyTest extends TestCase
{
    public function testWithdrawReducesBalance(): void
    {
        $accountId = BankAccountId::fromString('acc-001');
        $account = BankAccount::create($accountId)->deposit(new Money(100, Currency::USD));
        $repository = $this->createMock(BankAccountRepository::class);
        $repository->method('findBy')->willReturn($account);
        /** @var BankAccount|null $savedAccount */
        $savedAccount = null;
        $repository->expects($this->once())->method('save')->willReturnCallback(
            function ($arg) use (&$savedAccount) {
                $savedAccount = $arg;
            }
        );
        $domainEventBus = $this->createStub(DomainEventBus::class);
        $handler = new WithdrawMoneyCommandHandler($repository, $domainEventBus);

        $handler->handle(new WithdrawMoneyCommand($accountId, new Money(30, Currency::USD)));

        $this->assertNotNull($savedAccount);
        /** @var BankAccount $savedAccount */
        $this->assertEquals(70, $savedAccount->getBalance()->amount);
    }

    public function testWithdrawPublishesMoneyWithdrawnEvent(): void
    {
        $accountId = BankAccountId::fromString('acc-001');
        $account = BankAccount::create($accountId)->deposit(new Money(100, Currency::USD));

        $repository = $this->createStub(BankAccountRepository::class);
        $repository->method('findBy')->willReturn($account);

        $publishedEvents = [];
        $domainEventBus = $this->createMock(DomainEventBus::class);
        $domainEventBus->expects($this->atLeastOnce())->method('publish')->willReturnCallback(
            /**
             * @param array<DomainEvent> $events
             */
            function (array $events) use (&$publishedEvents) {
                $publishedEvents = array_merge($publishedEvents, $events);
            }
        );

        $handler = new WithdrawMoneyCommandHandler($repository, $domainEventBus);
        $handler->handle(new WithdrawMoneyCommand($accountId, new Money(30, Currency::USD)));

        $withdrawnEvents = array_values(array_filter($publishedEvents, fn ($e) => $e instanceof MoneyWithdrawn));
        $this->assertCount(1, $withdrawnEvents);
        $this->assertInstanceOf(MoneyWithdrawn::class, $withdrawnEvents[0]);
        $this->assertSame('acc-001', $withdrawnEvents[0]->accountId->value);
        $this->assertEquals(30, $withdrawnEvents[0]->amount->amount);
    }

    public function testWithdrawInsufficientFundsThrows(): void
    {
        $accountId = BankAccountId::fromString('acc-001');
        $account = BankAccount::create($accountId)->deposit(new Money(50, Currency::USD));

        $repository = $this->createStub(BankAccountRepository::class);
        $repository->method('findBy')->willReturn($account);
        $domainEventBus = $this->createStub(DomainEventBus::class);

        $handler = new WithdrawMoneyCommandHandler($repository, $domainEventBus);

        $this->expectException(InsufficientFundsException::class);

        $handler->handle(new WithdrawMoneyCommand($accountId, new Money(100, Currency::USD)));
    }
}
