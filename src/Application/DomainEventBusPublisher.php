<?php

declare(strict_types=1);

namespace SeedWork\Application;

use SeedWork\Domain\DomainEvent;

/**
 * Publisher port of the domain event bus — publishes events to the buffer.
 *
 * Segregated from {@see DomainEventBusSubscriber} so repositories and aggregates
 * depend only on the publish capability, not on handler registration.
 *
 * @see DomainEventBus Full bus combining publish, subscribe, dispatch and discard.
 */
interface DomainEventBusPublisher
{
    /**
     * @param array<DomainEvent> $events Events to buffer.
     */
    public function publish(array $events): void;
}
