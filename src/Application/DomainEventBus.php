<?php

declare(strict_types=1);

namespace SeedWork\Application;

use SeedWork\Domain\DomainEvent;

/**
 * Application port for publishing and subscribing to Domain Events.
 *
 * @see DomainEvent Events published through this bus.
 * @see DomainEventHandler Handlers registered via subscribe(); handle() is invoked on publish.
 */
interface DomainEventBus
{
    /**
     * @param array<DomainEvent> $events Events to publish.
     */
    public function publish(array $events): void;

    /**
     * @param string $eventType FQCN of the domain event (e.g. MoneyDeposited::class).
     * @param DomainEventHandler<DomainEvent> $handler Handler instance to invoke.
     */
    public function subscribe(string $eventType, DomainEventHandler $handler): void;
}
