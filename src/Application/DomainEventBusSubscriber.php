<?php

declare(strict_types=1);

namespace SeedWork\Application;

use SeedWork\Domain\DomainEvent;

/**
 * Subscriber port of the domain event bus — registers handlers for event types.
 *
 * Segregated from {@see DomainEventBusPublisher} to respect the Interface
 * Segregation Principle: components that only subscribe do not need to publish.
 *
 * @see DomainEventBus Full bus combining publish, subscribe, dispatch and discard.
 */
interface DomainEventBusSubscriber
{
    /**
     * @param string                          $eventType FQCN of the domain event (e.g. MoneyDeposited::class).
     * @param DomainEventHandler<DomainEvent> $handler   handler instance to invoke
     */
    public function subscribe(string $eventType, DomainEventHandler $handler): void;
}
