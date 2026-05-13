<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application port for publishing integration events to external systems.
 *
 * Implementations deliver events to a message broker, an outbox table for
 * reliable delivery, or an in-memory buffer for testing.
 *
 * @see IntegrationEvent Events published through this port.
 * @see IntegrationEventPublisherSpy Spy extension for test introspection.
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
