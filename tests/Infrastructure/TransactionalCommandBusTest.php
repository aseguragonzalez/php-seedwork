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

/**
 * @internal
 *
 * @coversNothing
 */
final class TransactionalCommandBusTest extends TestCase
{
    public function testDispatchDelegatesToInnerCommandBusWithSameCommand(): void
    {
        $command = new TestCommand();
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn(Result::ok())
        ;

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

    public function testRollbackIsCalledAndFailedResultReturnedWhenResultIsFailed(): void
    {
        $failed = Result::failed([new ResultError('err', 'fail')]);
        $innerBus = $this->createStub(CommandBus::class);
        $innerBus->method('dispatch')->willReturn($failed);

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->once())->method('createSession');
        $unitOfWork->expects($this->never())->method('commit');
        $unitOfWork->expects($this->once())->method('rollback');

        $bus = new TransactionalCommandBus($innerBus, $unitOfWork);
        $result = $bus->dispatch(new TestCommand());

        $this->assertSame($failed, $result);
        $this->assertTrue($result->isFailed());
        $this->assertSame('err', $result->errors()[0]->code);
    }

    public function testRollbackIsCalledAndExceptionRethrownWhenCommitThrows(): void
    {
        $innerBus = $this->createStub(CommandBus::class);
        $innerBus->method('dispatch')->willReturn(Result::ok());

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->once())->method('createSession');
        $unitOfWork->expects($this->once())->method('commit')
            ->willThrowException(new \RuntimeException('Commit failed'))
        ;
        $unitOfWork->expects($this->once())->method('rollback');

        $bus = new TransactionalCommandBus($innerBus, $unitOfWork);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Commit failed');

        $bus->dispatch(new TestCommand());
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
