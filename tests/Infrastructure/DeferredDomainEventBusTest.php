<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\DomainEventHandler;
use SeedWork\Testing\DeferredDomainEventBusSpy;
use Tests\Fixtures\AnotherTestEvent;
use Tests\Fixtures\TestEvent;

final class DeferredDomainEventBusTest extends TestCase
{
    public function testDispatchDispatchesToSubscribedHandlersByEventType(): void
    {
        $event = TestEvent::create();
        $handler = $this->createMock(DomainEventHandler::class);
        $handler->expects($this->once())->method('handle')->with($event);
        $bus = new DeferredDomainEventBusSpy();

        $bus->subscribe(TestEvent::class, $handler);
        $bus->publish([$event]);
        $bus->dispatch();
    }

    public function testDispatchSkipsEventsWithNoSubscribedHandlers(): void
    {
        $bus = new DeferredDomainEventBusSpy();
        $event = TestEvent::create();
        $bus->publish([$event]);
        $bus->dispatch();

        $this->addToAssertionCount(1);
    }

    public function testDispatchesMultipleEventTypesToTheirHandlers(): void
    {
        $first = TestEvent::create();
        $second = AnotherTestEvent::create();
        $handlerFirst = $this->createMock(DomainEventHandler::class);
        $handlerFirst->expects($this->once())->method('handle')->with($first);
        $handlerSecond = $this->createMock(DomainEventHandler::class);
        $handlerSecond->expects($this->once())->method('handle')->with($second);
        $bus = new DeferredDomainEventBusSpy();
        $bus->subscribe(TestEvent::class, $handlerFirst);
        $bus->subscribe(AnotherTestEvent::class, $handlerSecond);
        $bus->publish([$first, $second]);

        $bus->dispatch();
    }

    public function testDispatchInvokesMultipleHandlersForSameEventType(): void
    {
        $event = TestEvent::create();
        $handler1 = $this->createMock(DomainEventHandler::class);
        $handler1->expects($this->once())->method('handle')->with($event);
        $handler2 = $this->createMock(DomainEventHandler::class);
        $handler2->expects($this->once())->method('handle')->with($event);
        $bus = new DeferredDomainEventBusSpy();
        $bus->subscribe(TestEvent::class, $handler1);
        $bus->subscribe(TestEvent::class, $handler2);
        $bus->publish([$event]);

        $bus->dispatch();
    }

    public function testDispatchClearsBufferAfterDispatch(): void
    {
        $event1 = TestEvent::create();
        $event2 = TestEvent::create();
        $received = [];

        $handler = $this->createMock(DomainEventHandler::class);
        $handler->expects($this->exactly(2))
            ->method('handle')
            ->willReturnCallback(function ($event) use (&$received): void {
                $received[] = $event;
            });

        $bus = new DeferredDomainEventBusSpy();
        $bus->subscribe(TestEvent::class, $handler);

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

        $bus = new DeferredDomainEventBusSpy();
        $bus->subscribe(TestEvent::class, $handler);
        $bus->publish([TestEvent::create()]);
        $bus->discard();
        $bus->dispatch();

        $this->addToAssertionCount(1);
    }

    public function testPublishIsIdempotentForSameEventId(): void
    {
        $event = TestEvent::create('test.event', 'evt-idempotent-test.1');

        $handler = $this->createMock(DomainEventHandler::class);
        $handler->expects($this->once())->method('handle');

        $bus = new DeferredDomainEventBusSpy();
        $bus->subscribe(TestEvent::class, $handler);

        // Publish the same event twice — handler must be invoked only once
        $bus->publish([$event]);
        $bus->publish([$event]);
        $bus->dispatch();
    }

    public function testPendingReturnsBufferedEvents(): void
    {
        $event1 = TestEvent::create();
        $event2 = AnotherTestEvent::create();
        $bus = new DeferredDomainEventBusSpy();

        $bus->publish([$event1, $event2]);

        $this->assertSame([$event1, $event2], $bus->pending());
    }

    public function testPendingReturnsEmptyAfterDispatch(): void
    {
        $bus = new DeferredDomainEventBusSpy();
        $bus->publish([TestEvent::create()]);
        $bus->dispatch();

        $this->assertSame([], $bus->pending());
    }

    public function testResetClearsPendingBuffer(): void
    {
        $handler = $this->createMock(DomainEventHandler::class);
        $handler->expects($this->never())->method('handle');

        $bus = new DeferredDomainEventBusSpy();
        $bus->subscribe(TestEvent::class, $handler);
        $bus->publish([TestEvent::create()]);

        $bus->reset();

        $this->assertSame([], $bus->pending());
        $bus->dispatch();
    }
}
