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

        $this->assertCount(2, $publisher->getPublished());
        $this->assertSame($event1, $publisher->getPublished()[0]);
        $this->assertSame($event2, $publisher->getPublished()[1]);
    }

    public function testPublishAccumulatesAcrossMultipleCalls(): void
    {
        $publisher = new InMemoryIntegrationEventPublisher();
        $publisher->publish([new FakeIntegrationEvent('evt-1')]);
        $publisher->publish([new FakeIntegrationEvent('evt-2')]);

        $this->assertCount(2, $publisher->getPublished());
    }

    public function testClearResetsPublishedList(): void
    {
        $publisher = new InMemoryIntegrationEventPublisher();
        $publisher->publish([new FakeIntegrationEvent('evt-1')]);

        $publisher->clear();

        $this->assertEmpty($publisher->getPublished());
    }

    public function testGetPublishedReturnsEmptyArrayInitially(): void
    {
        $publisher = new InMemoryIntegrationEventPublisher();

        $this->assertSame([], $publisher->getPublished());
    }
}
