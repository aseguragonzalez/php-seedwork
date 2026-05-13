<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Infrastructure\IntegrationEventOutboxRepository;
use SeedWork\Infrastructure\OutboxIntegrationEventPublisher;
use Tests\Fixtures\FakeIntegrationEvent;

final class OutboxIntegrationEventPublisherTest extends TestCase
{
    public function testPublishSavesEachEventToRepository(): void
    {
        $event1 = new FakeIntegrationEvent('evt-001');
        $event2 = new FakeIntegrationEvent('evt-002');
        $callCount = 0;
        $repository = $this->createMock(IntegrationEventOutboxRepository::class);
        $repository->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function ($event) use ($event1, $event2, &$callCount): void {
                $callCount++;
                if ($callCount === 1) {
                    $this->assertSame($event1, $event);
                } else {
                    $this->assertSame($event2, $event);
                }
            });
        $publisher = new OutboxIntegrationEventPublisher($repository);

        $publisher->publish([$event1, $event2]);
    }

    public function testPublishWithEmptyArrayDoesNotCallSave(): void
    {
        $repository = $this->createMock(IntegrationEventOutboxRepository::class);
        $repository->expects($this->never())->method('save');
        $publisher = new OutboxIntegrationEventPublisher($repository);

        $publisher->publish([]);
    }

    public function testPublishSavesSingleEvent(): void
    {
        $event = new FakeIntegrationEvent('evt-001');
        $repository = $this->createMock(IntegrationEventOutboxRepository::class);
        $repository->expects($this->once())->method('save')->with($event);
        $publisher = new OutboxIntegrationEventPublisher($repository);

        $publisher->publish([$event]);
    }
}
