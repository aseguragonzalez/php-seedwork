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
use Tests\Fixtures\TestCommand;
use Tests\Fixtures\TestEvent;

/**
 * @internal
 *
 * @coversNothing
 */
final class DomainEventCoordinatorCommandBusTest extends TestCase
{
    public function testDispatchCallsEventBusDispatchWhenResultIsOk(): void
    {
        $event = TestEvent::create();
        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->once())->method('handle')->with($event);

        $eventBus = new DeferredDomainEventBus();
        $eventBus->subscribe(TestEvent::class, $eventHandler);
        $eventBus->publish([$event]);

        $innerBus = $this->createInnerBusReturning(Result::ok());

        $decorator = new DomainEventCoordinatorCommandBus($innerBus, $eventBus);
        $result = $decorator->dispatch(new TestCommand());

        $this->assertTrue($result->isOk());
    }

    public function testDispatchCallsEventBusDiscardWhenResultIsFailed(): void
    {
        $event = TestEvent::create();
        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->never())->method('handle');

        $eventBus = new DeferredDomainEventBus();
        $eventBus->subscribe(TestEvent::class, $eventHandler);
        $eventBus->publish([$event]);

        $innerBus = $this->createInnerBusReturning(Result::failed([new ResultError('err', 'fail')]));

        $decorator = new DomainEventCoordinatorCommandBus($innerBus, $eventBus);
        $result = $decorator->dispatch(new TestCommand());

        $this->assertTrue($result->isFailed());

        // Verify buffer was discarded: a subsequent dispatch should not call handlers
        $eventBus->dispatch();
    }

    public function testDispatchDiscardsEventBusAndRethrowsOnException(): void
    {
        $event = TestEvent::create();
        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->never())->method('handle');

        $eventBus = new DeferredDomainEventBus();
        $eventBus->subscribe(TestEvent::class, $eventHandler);
        $eventBus->publish([$event]);

        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())
            ->method('dispatch')
            ->willThrowException(new \RuntimeException('infrastructure failure'))
        ;

        $decorator = new DomainEventCoordinatorCommandBus($innerBus, $eventBus);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('infrastructure failure');

        $decorator->dispatch(new TestCommand());
    }

    public function testExceptionDiscardsBufferSoSubsequentDispatchIsClean(): void
    {
        $event = TestEvent::create();
        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->never())->method('handle');

        $eventBus = new DeferredDomainEventBus();
        $eventBus->subscribe(TestEvent::class, $eventHandler);
        $eventBus->publish([$event]);

        $innerBus = $this->createStub(CommandBus::class);
        $innerBus->method('dispatch')
            ->willThrowException(new \RuntimeException('infra error'))
        ;

        $decorator = new DomainEventCoordinatorCommandBus($innerBus, $eventBus);

        try {
            $decorator->dispatch(new TestCommand());
        } catch (\RuntimeException) {
        }

        // Buffer was discarded — a second dispatch should be a no-op (no handler calls)
        $eventBus->dispatch();
    }

    public function testEventBusDispatchExceptionDiscardsAndRethrows(): void
    {
        $eventBus = $this->createMock(DomainEventBus::class);
        $eventBus->expects($this->once())->method('dispatch')
            ->willThrowException(new \RuntimeException('handler failure'))
        ;
        $eventBus->expects($this->once())->method('discard');

        $innerBus = $this->createInnerBusReturning(Result::ok());
        $decorator = new DomainEventCoordinatorCommandBus($innerBus, $eventBus);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('handler failure');

        $decorator->dispatch(new TestCommand());
    }

    public function testAcceptsDomainEventBusInterface(): void
    {
        $eventBus = $this->createMock(DomainEventBus::class);
        $eventBus->expects($this->once())->method('dispatch');

        $innerBus = $this->createInnerBusReturning(Result::ok());

        $decorator = new DomainEventCoordinatorCommandBus($innerBus, $eventBus);
        $decorator->dispatch(new TestCommand());
    }

    private function createInnerBusReturning(Result $result): CommandBus
    {
        $inner = $this->createStub(CommandBus::class);
        $inner->method('dispatch')->willReturn($result);

        return $inner;
    }
}
