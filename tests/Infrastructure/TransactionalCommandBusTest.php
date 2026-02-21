<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandBus;
use SeedWork\Domain\UnitOfWork;
use SeedWork\Infrastructure\TransactionalCommandBus;
use Tests\Fixtures\BankAccount\Application\DepositMoney\DepositMoneyCommand;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final class TransactionalCommandBusTest extends TestCase
{
    public function testDispatchDelegatesToInnerCommandBusWithSameCommand(): void
    {
        $command = $this->createDepositMoneyCommand();
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())->method('dispatch')->with($command);

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->once())->method('createSession');
        $unitOfWork->expects($this->once())->method('commit');
        $unitOfWork->expects($this->never())->method('rollback');

        $bus = new TransactionalCommandBus($innerBus, $unitOfWork);
        $bus->dispatch($command);
    }

    public function testCommitIsCalledAfterSuccessfulDispatch(): void
    {
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())->method('dispatch');

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->once())->method('createSession');
        $unitOfWork->expects($this->once())->method('commit');
        $unitOfWork->expects($this->never())->method('rollback');

        $bus = new TransactionalCommandBus($innerBus, $unitOfWork);
        $bus->dispatch($this->createDepositMoneyCommand());
    }

    public function testRollbackIsCalledAndExceptionRethrownWhenDispatchThrows(): void
    {
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())->method('dispatch')->willThrowException(
            new \RuntimeException('Command failed')
        );

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->once())->method('createSession');
        $unitOfWork->expects($this->never())->method('commit');
        $unitOfWork->expects($this->once())->method('rollback');

        $bus = new TransactionalCommandBus($innerBus, $unitOfWork);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Command failed');

        $bus->dispatch($this->createDepositMoneyCommand());
    }

    private function createDepositMoneyCommand(): DepositMoneyCommand
    {
        return new DepositMoneyCommand(
            BankAccountId::create(),
            new Money(100, Currency::USD)
        );
    }
}
