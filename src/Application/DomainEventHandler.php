<?php

declare(strict_types=1);

namespace SeedWork\Application;

use SeedWork\Domain\DomainEvent;

/**
 * Contract for reacting to a single type of Domain Event.
 *
 * Handlers are registered on a {@see DomainEventBus} via subscribe($eventType, $handler).
 * When an event of that type is published, the bus invokes handle() with the event.
 * Use one handler per event type and concern (e.g. update read model, send notification,
 * publish integration message); keep handlers side-effectful but narrow. For async
 * buses, implement handle() in an idempotent way when events may be redelivered.
 *
 * @template TEvent of DomainEvent The domain event type this handler subscribes to.
 * @see DomainEvent Events passed to handle().
 * @see DomainEventBus Handlers are registered and invoked by the bus via handle().
 * @see https://martinfowler.com/eaaDev/DomainEvent.html Martin Fowler, P of EAA – Domain Event.
 */
interface DomainEventHandler
{
    /**
     * Handles the domain event. Invoked by the event bus when an event of the
     * type this handler was subscribed for is published.
     *
     * @param TEvent $event The domain event to handle.
     */
    public function handle(DomainEvent $event): void;
}
