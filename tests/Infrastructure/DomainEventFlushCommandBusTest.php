<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\Command;
use SeedWork\Application\CommandBus;
use SeedWork\Application\CommandHandler;
use SeedWork\Application\DomainEventHandler;
use SeedWork\Application\Result;
use SeedWork\Application\ResultError;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\DomainEventFlushCommandBus;
use Examples\BankAccount\Application\DepositMoney\DepositMoneyCommand;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Entities\TransactionId;
use Examples\BankAccount\Domain\Events\MoneyDeposited;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Domain\ValueObjects\Money;

final class DomainEventFlushCommandBusTest extends TestCase
{
    public function testDispatchFlushesEventBusWhenResultIsOk(): void
    {
        $event = $this->createMoneyDepositedEvent();
        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->once())->method('handle')->with($event);

        $eventBus = new DeferredDomainEventBus();
        $eventBus->subscribe(MoneyDeposited::class, $eventHandler);
        $eventBus->publish([$event]);

        $innerBus = $this->createInnerBusReturning(Result::ok());

        $decorator = new DomainEventFlushCommandBus($innerBus, $eventBus);
        $result = $decorator->dispatch($this->createDepositMoneyCommand());

        $this->assertTrue($result->isOk());
    }

    public function testDispatchClearsEventBusWhenResultIsFailed(): void
    {
        $event = $this->createMoneyDepositedEvent();
        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->never())->method('handle');

        $eventBus = new DeferredDomainEventBus();
        $eventBus->subscribe(MoneyDeposited::class, $eventHandler);
        $eventBus->publish([$event]);

        $innerBus = $this->createInnerBusReturning(Result::failed([new ResultError('err', 'fail')]));

        $decorator = new DomainEventFlushCommandBus($innerBus, $eventBus);
        $result = $decorator->dispatch($this->createDepositMoneyCommand());

        $this->assertTrue($result->isFail());

        // Verify buffer was cleared: a subsequent flush should not call handlers
        $eventBus->flush();
    }

    public function testDispatchPropagatesExceptionWithoutFlushingOrClearing(): void
    {
        $event = $this->createMoneyDepositedEvent();
        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->never())->method('handle');

        $eventBus = new DeferredDomainEventBus();
        $eventBus->subscribe(MoneyDeposited::class, $eventHandler);
        $eventBus->publish([$event]);

        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())
            ->method('dispatch')
            ->willThrowException(new \RuntimeException('infrastructure failure'));

        $decorator = new DomainEventFlushCommandBus($innerBus, $eventBus);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('infrastructure failure');

        $decorator->dispatch($this->createDepositMoneyCommand());
    }

    private function createDepositMoneyCommand(): DepositMoneyCommand
    {
        return new DepositMoneyCommand(
            BankAccountId::create()->value,
            100,
            'USD'
        );
    }

    private function createMoneyDepositedEvent(): MoneyDeposited
    {
        return MoneyDeposited::create(
            new Money(100, Currency::USD),
            BankAccountId::create(),
            TransactionId::create()
        );
    }

    private function createInnerBusReturning(Result $result): CommandBus
    {
        $inner = $this->createMock(CommandBus::class);
        $inner->method('dispatch')->willReturn($result);
        return $inner;
    }
}
