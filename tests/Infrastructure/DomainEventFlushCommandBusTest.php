<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use Seedwork\Application\CommandBus;
use Seedwork\Application\DomainEventHandler;
use Seedwork\Domain\DomainEvent;
use Seedwork\Infrastructure\DeferredDomainEventBus;
use Seedwork\Infrastructure\DomainEventFlushCommandBus;
use Tests\Fixtures\BankAccount\Application\DepositMoney\DepositMoneyCommand;
use Tests\Fixtures\BankAccount\Application\MoneyDeposited\MoneyDepositedEventHandler;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Entities\TransactionId;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyDeposited;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;
use Tests\Fixtures\FakeContainer;

final class DomainEventFlushCommandBusTest extends TestCase
{
    public function testDispatchDelegatesToInnerCommandBusWithSameCommand(): void
    {
        $command = $this->createDepositMoneyCommand();
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())->method('dispatch')->with($command);

        $eventBus = new DeferredDomainEventBus(new FakeContainer([]));

        $decorator = new DomainEventFlushCommandBus($innerBus, $eventBus);
        $decorator->dispatch($command);
    }

    public function testFlushIsCalledAfterDispatch(): void
    {
        $event = MoneyDeposited::create(
            new Money(100, Currency::USD),
            BankAccountId::create(),
            TransactionId::create()
        );
        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->once())->method('handle')->with($event);

        $container = new FakeContainer([MoneyDepositedEventHandler::class => $eventHandler]);
        $eventBus = new DeferredDomainEventBus($container);
        $eventBus->subscribe(MoneyDeposited::class, MoneyDepositedEventHandler::class);

        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())->method('dispatch')->willReturnCallback(
            function () use ($eventBus, $event): void {
                $eventBus->publish([$event]);
            }
        );

        $decorator = new DomainEventFlushCommandBus($innerBus, $eventBus);
        $decorator->dispatch($this->createDepositMoneyCommand());
    }

    public function testFlushIsNotCalledWhenInnerBusThrows(): void
    {
        $event = MoneyDeposited::create(
            new Money(100, Currency::USD),
            BankAccountId::create(),
            TransactionId::create()
        );
        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->never())->method('handle');

        $container = new FakeContainer([MoneyDepositedEventHandler::class => $eventHandler]);
        $eventBus = new DeferredDomainEventBus($container);
        $eventBus->subscribe(MoneyDeposited::class, MoneyDepositedEventHandler::class);

        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())->method('dispatch')->willReturnCallback(
            function () use ($eventBus, $event): void {
                $eventBus->publish([$event]);
                throw new \RuntimeException('Command failed');
            }
        );

        $decorator = new DomainEventFlushCommandBus($innerBus, $eventBus);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Command failed');

        $decorator->dispatch($this->createDepositMoneyCommand());
    }

    private function createDepositMoneyCommand(): DepositMoneyCommand
    {
        return new DepositMoneyCommand(
            BankAccountId::create(),
            new Money(100, Currency::USD)
        );
    }
}
