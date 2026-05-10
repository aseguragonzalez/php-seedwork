<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\DomainEventBus;
use SeedWork\Application\DomainEventHandler;
use SeedWork\Domain\DomainEvent;

/**
 * Registry-based implementation of {@see DomainEventBus} that buffers events and
 * dispatches them only when flush() is called (deferred dispatch).
 *
 * @see DomainEventBus Application port.
 * @see DomainEventHandler Handlers registered via subscribe() and invoked at flush.
 */
final class DeferredDomainEventBus implements DomainEventBus
{
    /** @var array<string, list<DomainEventHandler<DomainEvent>>> */
    private array $handlers = [];
    /** @var list<DomainEvent> */
    private array $pending = [];

    /**
     * @param string $eventType FQCN of the domain event.
     * @param DomainEventHandler<DomainEvent> $handler Handler instance.
     */
    public function subscribe(string $eventType, DomainEventHandler $handler): void
    {
        $this->handlers[$eventType][] = $handler;
    }

    /**
     * @param array<DomainEvent> $events Events to buffer.
     */
    public function publish(array $events): void
    {
        foreach ($events as $event) {
            $this->pending[] = $event;
        }
    }

    public function flush(): void
    {
        $events = $this->pending;
        $this->pending = [];
        foreach ($events as $event) {
            $handlers = $this->handlers[$event::class] ?? [];
            foreach ($handlers as $handler) {
                $handler->handle($event);
            }
        }
    }

    public function clear(): void
    {
        $this->pending = [];
    }
}
