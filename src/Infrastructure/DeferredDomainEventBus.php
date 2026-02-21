<?php

declare(strict_types=1);

namespace Seedwork\Infrastructure;

use Psr\Container\ContainerInterface;
use Seedwork\Application\DomainEventBus;
use Seedwork\Application\DomainEventHandler;
use Seedwork\Domain\DomainEvent;

/**
 * PSR-11-based implementation of DomainEventBus that buffers events and
 * dispatches them only when flush() is called (deferred dispatch). Handlers are
 * resolved by event class name via a container.
 *
 * Usage: (1) Construct with a ContainerInterface.
 * (2) Call subscribe($eventType, $handlerServiceId) for each event type (same
 *     event type can have multiple handlers).
 * (3) Call publish($events) to append events to the buffer (no dispatch yet).
 * (4) Call flush() to drain the buffer and dispatch each event to all subscribed
 *     handlers; events with no subscribers are skipped.
 *
 * Implementation: Events are keyed by $event::class; subscription map is event
 * FQCN to list of container service IDs. Handlers are resolved from the container
 * at flush time. After flush, the buffer is cleared so each event is dispatched
 * only once. Order of dispatch: by order of events in the buffer, then by order
 * of handlers per event type.
 *
 * @throws \InvalidArgumentException When the container returns a service that does
 *         not implement DomainEventHandler for a subscribed handler ID.
 */
final class DeferredDomainEventBus implements DomainEventBus
{
    /**
     * @param ContainerInterface           $container PSR-11 container for resolving handlers.
     * @param array<DomainEvent>           $buffer    Optional initial events to buffer.
     * @param array<string, array<string>> $handlers Map of event FQCN to list of container service IDs for handlers.
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private array $buffer = [],
        private array $handlers = []
    ) {
    }

    /**
     * Appends events to the internal buffer. No dispatch occurs until flush() is called.
     *
     * @param array<DomainEvent> $events Events to buffer.
     */
    public function publish(array $events): void
    {
        $this->buffer = array_merge($this->buffer, $events);
    }

    /**
     * Subscribes a handler to an event type. Multiple handlers can be subscribed
     * to the same event type; each call appends.
     *
     * @param string $eventType           Event class name (FQCN).
     * @param string $domainEventHandler  Container service ID for the handler.
     */
    public function subscribe(string $eventType, string $domainEventHandler): void
    {
        if (!isset($this->handlers[$eventType])) {
            $this->handlers[$eventType] = [];
        }
        $this->handlers[$eventType][] = $domainEventHandler;
    }

    /**
     * Drains the buffer and dispatches each buffered event to all handlers
     * subscribed to its class. Event types with no subscribers are skipped.
     * The buffer is cleared after copying, so each event is dispatched only once.
     *
     * @throws \InvalidArgumentException When the container returns a service that
     *         does not implement DomainEventHandler for a subscribed handler.
     */
    public function flush(): void
    {
        $events = $this->buffer;
        $this->buffer = [];

        foreach ($events as $event) {
            $eventType = $event::class;
            if (!isset($this->handlers[$eventType])) {
                continue;
            }

            foreach ($this->handlers[$eventType] as $handlerClass) {
                $handler = $this->container->get($handlerClass);
                if (!$handler instanceof DomainEventHandler) {
                    throw new \InvalidArgumentException(
                        sprintf('Handler for event type %s is not a valid handler.', $eventType)
                    );
                }
                $handler->handle($event);
            }
        }
    }
}
