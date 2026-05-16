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
 */
interface IntegrationEventPublisher
{
    /**
     * Publishes a single integration event.
     */
    public function publish(IntegrationEvent $event): void;
}
