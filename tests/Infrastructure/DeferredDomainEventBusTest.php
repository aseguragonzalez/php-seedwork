<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\DomainEventHandler;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Entities\TransactionId;
use Examples\BankAccount\Domain\Events\BankAccountEventId;
use Examples\BankAccount\Domain\Events\MoneyDeposited;
use Examples\BankAccount\Domain\Events\MoneyWithdrawn;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Domain\ValueObjects\Money;

final class DeferredDomainEventBusTest extends TestCase
{
    public function testDispatchDispatchesToSubscribedHandlersByEventType(): void
    {
        $event = $this->createMoneyDepositedEvent();
        $handler = $this->createMock(DomainEventHandler::class);
        $handler->expects($this->once())->method('handle')->with($event);
        $bus = new DeferredDomainEventBus();

        $bus->subscribe(MoneyDeposited::class, $handler);
        $bus->publish([$event]);
        $bus->dispatch();
    }

    public function testDispatchSkipsEventsWithNoSubscribedHandlers(): void
    {
        $bus = new DeferredDomainEventBus();
        $event = $this->createMoneyDepositedEvent();
        $bus->publish([$event]);
        $bus->dispatch();

        $this->addToAssertionCount(1);
    }

    public function testDispatchesMultipleEventTypesToTheirHandlers(): void
    {
        $deposited = $this->createMoneyDepositedEvent();
        $withdrawn = $this->createMoneyWithdrawnEvent();
        $handlerDeposit = $this->createMock(DomainEventHandler::class);
        $handlerDeposit->expects($this->once())->method('handle')->with($deposited);
        $handlerWithdraw = $this->createMock(DomainEventHandler::class);
        $handlerWithdraw->expects($this->once())->method('handle')->with($withdrawn);
        $bus = new DeferredDomainEventBus();
        $bus->subscribe(MoneyDeposited::class, $handlerDeposit);
        $bus->subscribe(MoneyWithdrawn::class, $handlerWithdraw);
        $bus->publish([$deposited, $withdrawn]);

        $bus->dispatch();
    }

    public function testDispatchInvokesMultipleHandlersForSameEventType(): void
    {
        $event = $this->createMoneyDepositedEvent();
        $handler1 = $this->createMock(DomainEventHandler::class);
        $handler1->expects($this->once())->method('handle')->with($event);
        $handler2 = $this->createMock(DomainEventHandler::class);
        $handler2->expects($this->once())->method('handle')->with($event);
        $bus = new DeferredDomainEventBus();
        $bus->subscribe(MoneyDeposited::class, $handler1);
        $bus->subscribe(MoneyDeposited::class, $handler2);
        $bus->publish([$event]);

        $bus->dispatch();
    }

    public function testDispatchClearsBufferAfterDispatch(): void
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

        $bus = new DeferredDomainEventBus();
        $bus->subscribe(MoneyDeposited::class, $handler);

        $bus->publish([$event1]);
        $bus->dispatch();

        $bus->publish([$event2]);
        $bus->dispatch();

        $this->assertSame([$event1, $event2], $received);
    }

    public function testDiscardDiscardsBufferedEventsWithoutDispatching(): void
    {
        $handler = $this->createMock(DomainEventHandler::class);
        $handler->expects($this->never())->method('handle');

        $bus = new DeferredDomainEventBus();
        $bus->subscribe(MoneyDeposited::class, $handler);
        $bus->publish([$this->createMoneyDepositedEvent()]);
        $bus->discard();
        $bus->dispatch();

        $this->addToAssertionCount(1);
    }

    public function testPublishIsIdempotentForSameEventId(): void
    {
        $eventId = BankAccountEventId::fromString('evt-idempotent-test.1');
        $event = $this->createMoneyDepositedEventWithId($eventId);

        $handler = $this->createMock(DomainEventHandler::class);
        $handler->expects($this->once())->method('handle');

        $bus = new DeferredDomainEventBus();
        $bus->subscribe(MoneyDeposited::class, $handler);

        // Publish the same event twice — handler must be invoked only once
        $bus->publish([$event]);
        $bus->publish([$event]);
        $bus->dispatch();
    }

    private function createMoneyDepositedEvent(): MoneyDeposited
    {
        return MoneyDeposited::create(
            new Money(100, Currency::USD),
            BankAccountId::create(),
            TransactionId::create()
        );
    }

    private function createMoneyDepositedEventWithId(BankAccountEventId $id): MoneyDeposited
    {
        return MoneyDeposited::create(
            new Money(100, Currency::USD),
            BankAccountId::create(),
            TransactionId::create(),
            $id
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
