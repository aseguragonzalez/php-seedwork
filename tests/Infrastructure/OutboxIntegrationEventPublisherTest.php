<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Infrastructure\IntegrationEventOutboxRepository;
use SeedWork\Infrastructure\OutboxIntegrationEventPublisher;
use Tests\Fixtures\FakeIntegrationEvent;

final class OutboxIntegrationEventPublisherTest extends TestCase
{
    public function testPublishSavesEventToRepository(): void
    {
        $event = new FakeIntegrationEvent('evt-001');
        $repository = $this->createMock(IntegrationEventOutboxRepository::class);
        $repository->expects($this->once())->method('save')->with($event);
        $publisher = new OutboxIntegrationEventPublisher($repository);

        $publisher->publish($event);
    }

    public function testPublishCalledTwiceSavesTwice(): void
    {
        $event1 = new FakeIntegrationEvent('evt-001');
        $event2 = new FakeIntegrationEvent('evt-002');
        $repository = $this->createMock(IntegrationEventOutboxRepository::class);
        $repository->expects($this->exactly(2))->method('save');
        $publisher = new OutboxIntegrationEventPublisher($repository);

        $publisher->publish($event1);
        $publisher->publish($event2);
    }
}
