<?php

declare(strict_types=1);

namespace Seedwork\Application;

use Seedwork\Domain\DomainEvent;

/**
 * Application port for publishing and subscribing to Domain Events.
 *
 * Decouples the application layer from how events are delivered: an in-process
 * implementation may dispatch synchronously to registered handlers; an
 * infrastructure implementation may push to a message queue for async
 * processing or other bounded contexts. Command handlers typically collect
 * events from an aggregate (e.g. after persist) and call publish() once per
 * transaction or unit of work.
 *
 * @see DomainEvent Events published through this bus.
 * @see DomainEventHandler Handlers registered via subscribe(); handle() is invoked on publish().
 * @see https://martinfowler.com/eaaDev/DomainEvent.html Martin Fowler, P of EAA – Domain Event.
 */
interface DomainEventBus
{
    /**
     * Publishes the given domain events. Implementations dispatch to all
     * handlers subscribed to each event's type (or push to a broker). Order
     * of dispatch and transactional boundaries are implementation-defined.
     *
     * @param array<DomainEvent> $events Events to publish (e.g. collected from an aggregate).
     */
    public function publish(array $events): void;

    /**
     * Registers a handler for events of the given type. When such an event
     * is published, the handler is invoked. Allows adding new reactions
     * (projections, side effects) without changing publishers (Open/Closed).
     *
     * @param string $eventType FQCN of the domain event (e.g. MoneyDeposited::class).
     * @param string $domainEventHandler FQCN of the domain event handler (e.g. MoneyDepositedEventHandler::class).
     */
    public function subscribe(string $eventType, string $domainEventHandler): void;
}
