<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Infrastructure\InMemoryIntegrationEventPublisher;
use Tests\Fixtures\FakeIntegrationEvent;

final class InMemoryIntegrationEventPublisherTest extends TestCase
{
    public function testPublishAddsEventsToPublishedList(): void
    {
        $publisher = new InMemoryIntegrationEventPublisher();
        $event1 = new FakeIntegrationEvent('evt-1');
        $event2 = new FakeIntegrationEvent('evt-2');

        $publisher->publish([$event1, $event2]);

        $this->assertCount(2, $publisher->published());
        $this->assertSame($event1, $publisher->published()[0]);
        $this->assertSame($event2, $publisher->published()[1]);
    }

    public function testPublishAccumulatesAcrossMultipleCalls(): void
    {
        $publisher = new InMemoryIntegrationEventPublisher();
        $publisher->publish([new FakeIntegrationEvent('evt-1')]);
        $publisher->publish([new FakeIntegrationEvent('evt-2')]);

        $this->assertCount(2, $publisher->published());
    }

    public function testResetClearsPublishedList(): void
    {
        $publisher = new InMemoryIntegrationEventPublisher();
        $publisher->publish([new FakeIntegrationEvent('evt-1')]);

        $publisher->reset();

        $this->assertEmpty($publisher->published());
    }

    public function testPublishedReturnsEmptyArrayInitially(): void
    {
        $publisher = new InMemoryIntegrationEventPublisher();

        $this->assertSame([], $publisher->published());
    }
}
