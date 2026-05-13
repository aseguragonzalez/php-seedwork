<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandBus;
use SeedWork\Application\DomainEventBus;
use SeedWork\Application\DomainEventHandler;
use SeedWork\Application\Result;
use SeedWork\Application\ResultError;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\DomainEventCoordinatorCommandBus;
use Examples\BankAccount\Application\DepositMoney\DepositMoneyCommand;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Entities\TransactionId;
use Examples\BankAccount\Domain\Events\MoneyDeposited;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Domain\ValueObjects\Money;

final class DomainEventCoordinatorCommandBusTest extends TestCase
{
    public function testDispatchCallsEventBusDispatchWhenResultIsOk(): void
    {
        $event = $this->createMoneyDepositedEvent();
        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->once())->method('handle')->with($event);

        $eventBus = new DeferredDomainEventBus();
        $eventBus->subscribe(MoneyDeposited::class, $eventHandler);
        $eventBus->publish([$event]);

        $innerBus = $this->createInnerBusReturning(Result::ok());

        $decorator = new DomainEventCoordinatorCommandBus($innerBus, $eventBus);
        $result = $decorator->dispatch($this->createDepositMoneyCommand());

        $this->assertTrue($result->isOk());
    }

    public function testDispatchCallsEventBusDiscardWhenResultIsFailed(): void
    {
        $event = $this->createMoneyDepositedEvent();
        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->never())->method('handle');

        $eventBus = new DeferredDomainEventBus();
        $eventBus->subscribe(MoneyDeposited::class, $eventHandler);
        $eventBus->publish([$event]);

        $innerBus = $this->createInnerBusReturning(Result::failed([new ResultError('err', 'fail')]));

        $decorator = new DomainEventCoordinatorCommandBus($innerBus, $eventBus);
        $result = $decorator->dispatch($this->createDepositMoneyCommand());

        $this->assertTrue($result->isFail());

        // Verify buffer was discarded: a subsequent dispatch should not call handlers
        $eventBus->dispatch();
    }

    public function testDispatchDiscardsEventBusAndRethrowsOnException(): void
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

        $decorator = new DomainEventCoordinatorCommandBus($innerBus, $eventBus);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('infrastructure failure');

        $decorator->dispatch($this->createDepositMoneyCommand());
    }

    public function testExceptionDiscardsBufferSoSubsequentDispatchIsClean(): void
    {
        $event = $this->createMoneyDepositedEvent();
        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->never())->method('handle');

        $eventBus = new DeferredDomainEventBus();
        $eventBus->subscribe(MoneyDeposited::class, $eventHandler);
        $eventBus->publish([$event]);

        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->method('dispatch')
            ->willThrowException(new \RuntimeException('infra error'));

        $decorator = new DomainEventCoordinatorCommandBus($innerBus, $eventBus);

        try {
            $decorator->dispatch($this->createDepositMoneyCommand());
        } catch (\RuntimeException) {
        }

        // Buffer was discarded — a second dispatch should be a no-op (no handler calls)
        $eventBus->dispatch();
    }

    public function testAcceptsDomainEventBusInterface(): void
    {
        $eventBus = $this->createMock(DomainEventBus::class);
        $eventBus->expects($this->once())->method('dispatch');

        $innerBus = $this->createInnerBusReturning(Result::ok());

        $decorator = new DomainEventCoordinatorCommandBus($innerBus, $eventBus);
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
