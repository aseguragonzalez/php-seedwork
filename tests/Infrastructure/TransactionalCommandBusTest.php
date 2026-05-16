<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandBus;
use SeedWork\Application\Result;
use SeedWork\Application\ResultError;
use SeedWork\Domain\UnitOfWork;
use SeedWork\Infrastructure\TransactionalCommandBus;
use Tests\Fixtures\TestCommand;

final class TransactionalCommandBusTest extends TestCase
{
    public function testDispatchDelegatesToInnerCommandBusWithSameCommand(): void
    {
        $command = new TestCommand();
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn(Result::ok());

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->once())->method('createSession');
        $unitOfWork->expects($this->once())->method('commit');
        $unitOfWork->expects($this->never())->method('rollback');

        $bus = new TransactionalCommandBus($innerBus, $unitOfWork);
        $result = $bus->dispatch($command);

        $this->assertTrue($result->isOk());
    }

    public function testCommitIsCalledAfterSuccessfulDispatch(): void
    {
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())->method('dispatch')->willReturn(Result::ok());

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->once())->method('createSession');
        $unitOfWork->expects($this->once())->method('commit');
        $unitOfWork->expects($this->never())->method('rollback');

        $bus = new TransactionalCommandBus($innerBus, $unitOfWork);
        $bus->dispatch(new TestCommand());
    }

    public function testCommitIsCalledEvenWhenResultIsFailed(): void
    {
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())->method('dispatch')
            ->willReturn(Result::failed([new ResultError('err', 'fail')]));

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->once())->method('createSession');
        $unitOfWork->expects($this->once())->method('commit');
        $unitOfWork->expects($this->never())->method('rollback');

        $bus = new TransactionalCommandBus($innerBus, $unitOfWork);
        $result = $bus->dispatch(new TestCommand());

        $this->assertTrue($result->isFailed());
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

        $bus->dispatch(new TestCommand());
    }
}
