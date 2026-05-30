<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Contract for handling a single type of integration event received from an external broker.
 *
 * Implement handle() idempotently — integration events may be delivered more than
 * once (at-least-once delivery guarantees).
 *
 * @see IntegrationEvent Events passed to handle().
 */
interface IntegrationEventHandler
{
    /**
     * Processes the integration event.
     *
     * @param IntegrationEvent $event the event to handle
     */
    public function handle(IntegrationEvent $event): void;
}
