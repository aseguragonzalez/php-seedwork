<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\DomainEventHandler;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use Tests\Fixtures\BankAccount\Application\DepositMoney\DepositMoney;
use Tests\Fixtures\BankAccount\Application\DepositMoney\DepositMoneyCommandHandler;
use Tests\Fixtures\BankAccount\Application\MoneyDeposited\MoneyDepositedEventHandler;
use Tests\Fixtures\BankAccount\Application\MoneyWithdrawn\MoneyWithdrawnEventHandler;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Entities\TransactionId;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyDeposited;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyWithdrawn;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;
use Tests\Fixtures\FakeContainer;

final class DeferredDomainEventBusTest extends TestCase
{
    public function testFlushDispatchesToSubscribedHandlersByEventType(): void
    {
        $event = $this->createMoneyDepositedEvent();
        $handler = $this->createMock(DomainEventHandler::class);
        $handler->expects($this->once())->method('handle')->with($event);
        $container = new FakeContainer([MoneyDepositedEventHandler::class => $handler]);
        $bus = new DeferredDomainEventBus($container);

        $bus->subscribe(MoneyDeposited::class, MoneyDepositedEventHandler::class);
        $bus->publish([$event]);
        $bus->flush();
    }

    public function testFlushSkipsEventsWithNoSubscribedHandlers(): void
    {
        $container = new FakeContainer([]);
        $bus = new DeferredDomainEventBus($container);
        $event = $this->createMoneyDepositedEvent();
        $bus->publish([$event]);
        $bus->flush();

        $this->addToAssertionCount(1);
    }

    public function testFlushDispatchesMultipleEventTypesToTheirHandlers(): void
    {
        $deposited = $this->createMoneyDepositedEvent();
        $withdrawn = $this->createMoneyWithdrawnEvent();
        $handlerDeposit = $this->createMock(DomainEventHandler::class);
        $handlerDeposit->expects($this->once())->method('handle')->with($deposited);
        $handlerWithdraw = $this->createMock(DomainEventHandler::class);
        $handlerWithdraw->expects($this->once())->method('handle')->with($withdrawn);
        $container = new FakeContainer([
            MoneyDepositedEventHandler::class => $handlerDeposit,
            MoneyWithdrawnEventHandler::class => $handlerWithdraw,
        ]);
        $bus = new DeferredDomainEventBus($container);
        $bus->subscribe(MoneyDeposited::class, MoneyDepositedEventHandler::class);
        $bus->subscribe(MoneyWithdrawn::class, MoneyWithdrawnEventHandler::class);
        $bus->publish([$deposited, $withdrawn]);

        $bus->flush();
    }

    public function testFlushInvokesMultipleHandlersForSameEventType(): void
    {
        $event = $this->createMoneyDepositedEvent();
        $handler1 = $this->createMock(DomainEventHandler::class);
        $handler1->expects($this->once())->method('handle')->with($event);
        $handler2 = $this->createMock(DomainEventHandler::class);
        $handler2->expects($this->once())->method('handle')->with($event);
        $container = new FakeContainer([
            MoneyDepositedEventHandler::class => $handler1,
            MoneyWithdrawnEventHandler::class => $handler2,
        ]);
        $bus = new DeferredDomainEventBus($container);
        $bus->subscribe(MoneyDeposited::class, MoneyDepositedEventHandler::class);
        $bus->subscribe(MoneyDeposited::class, MoneyWithdrawnEventHandler::class);
        $bus->publish([$event]);

        $bus->flush();
    }

    public function testFlushClearsBufferAfterDispatch(): void
    {
        $event1 = $this->createMoneyDepositedEvent();
        $event2 = $this->createMoneyDepositedEvent();
        $received = [];

        $handler = $this->createMock(DomainEventHandler::class);
        $handler->expects($this->exactly(2))
            ->method('handle')
            ->willReturnCallback(function ($event) use (&$received): void {
                $received[] = $event;
            });

        $container = new FakeContainer(['handler' => $handler]);
        $bus = new DeferredDomainEventBus($container);

        $bus->subscribe(MoneyDeposited::class, 'handler');

        $bus->publish([$event1]);
        $bus->flush();

        $bus->publish([$event2]);
        $bus->flush();

        $this->assertSame([$event1, $event2], $received);
    }

    public function testFlushThrowsWhenContainerReturnsNonHandler(): void
    {
        $mock = $this->createStub(DepositMoney::class);
        $container = new FakeContainer([DepositMoneyCommandHandler::class => $mock]);
        $bus = new DeferredDomainEventBus($container);
        $bus->subscribe(MoneyDeposited::class, DepositMoneyCommandHandler::class);
        $bus->publish([$this->createMoneyDepositedEvent()]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Handler for event type Tests\Fixtures\BankAccount\Domain\Events\MoneyDeposited is not a valid handler.'
        );

        $bus->flush();
    }

    private function createMoneyDepositedEvent(): MoneyDeposited
    {
        return MoneyDeposited::create(
            new Money(100, Currency::USD),
            BankAccountId::create(),
            TransactionId::create()
        );
    }

    private function createMoneyWithdrawnEvent(): MoneyWithdrawn
    {
        return MoneyWithdrawn::create(
            new Money(50, Currency::USD),
            BankAccountId::create(),
            TransactionId::create()
        );
    }
}
