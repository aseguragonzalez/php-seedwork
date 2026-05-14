<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\IntegrationEvent;
use SeedWork\Application\IntegrationEventPublisher;

/**
 * {@see IntegrationEventPublisher} that persists events via the transactional
 * outbox pattern. Each event is stored as a pending {@see IntegrationEventOutboxRecord}
 * in the {@see IntegrationEventOutboxRepository} for reliable async delivery.
 *
 * @see IntegrationEventOutboxRepository Repository that stores the outbox records.
 * @see IntegrationEvent                 Events stored by this publisher.
 */
final class OutboxIntegrationEventPublisher implements IntegrationEventPublisher
{
    public function __construct(
        private readonly IntegrationEventOutboxRepository $repository
    ) {
    }

    /**
     * @param array<IntegrationEvent> $events
     */
    public function publish(array $events): void
    {
        foreach ($events as $event) {
            $this->repository->save($event);
        }
    }
}
