<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application port for publishing integration events to external systems.
 *
 * Implementations deliver events to a message broker, outbox table, or
 * in-memory store. The {@see OutboxIntegrationEventPublisher} persists events
 * via the outbox pattern for reliable delivery.
 *
 * @see IntegrationEvent Events published through this port.
 * @see SeedWork\Infrastructure\IntegrationEventOutboxRepository Outbox-based implementation.
 */
interface IntegrationEventPublisher
{
    /**
     * Publishes the given integration events.
     *
     * @param array<IntegrationEvent> $events Events to publish.
     */
    public function publish(array $events): void;
}
