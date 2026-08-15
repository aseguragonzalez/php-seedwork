<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\DomainEventBus;
use SeedWork\Application\DomainEventHandler;
use SeedWork\Domain\DomainEvent;

/**
 * Registry-based implementation of {@see DomainEventBus} that buffers events and
 * dispatches them only when dispatch() is called (deferred dispatch).
 *
 * The pending buffer is keyed by event id (string) for idempotency: publishing the
 * same event twice (same id) results in a single dispatch. This prevents
 * double-handling when aggregates share events across calls.
 *
 * For test use, extend with SeedWork\Testing\DeferredDomainEventBusSpy to
 * gain the typed SeedWork\Testing\DomainEventBusSpy contract.
 *
 * Guards against reentrant publish()/dispatch()/discard() calls while a dispatch()
 * is already in progress on the same instance, throwing \LogicException instead of
 * silently corrupting the pending buffer.
 *
 * @see DomainEventBus Application port.
 * @see DomainEventHandler Handlers registered via subscribe() and invoked at dispatch.
 */
class DeferredDomainEventBus implements DomainEventBus
{
    /** @var array<string, DomainEvent> keyed by event id for idempotency */
    protected array $pending = [];

    /** @var array<string, list<DomainEventHandler<DomainEvent>>> */
    private array $handlers = [];

    private bool $dispatching = false;

    /**
     * @param string                          $eventType FQCN of the domain event
     * @param DomainEventHandler<DomainEvent> $handler   handler instance
     */
    public function subscribe(string $eventType, DomainEventHandler $handler): void
    {
        $this->handlers[$eventType][] = $handler;
    }

    /**
     * Buffers events by id; duplicate ids (same event published twice) are ignored.
     *
     * @param array<DomainEvent> $events events to buffer
     */
    public function publish(array $events): void
    {
        $this->guardAgainstReentrancy();

        foreach ($events as $event) {
            $id = $event->id;
            if (!isset($this->pending[$id])) {
                $this->pending[$id] = $event;
            }
        }
    }

    /**
     * Dispatches all buffered events to their registered handlers, then clears the buffer.
     */
    public function dispatch(): void
    {
        $this->guardAgainstReentrancy();

        $this->dispatching = true;

        try {
            $events = array_values($this->pending);
            $this->pending = [];
            foreach ($events as $event) {
                $handlers = $this->handlers[$event::class] ?? [];
                foreach ($handlers as $handler) {
                    $handler->handle($event);
                }
            }
        } finally {
            $this->dispatching = false;
        }
    }

    /**
     * Clears the buffer without dispatching. Use when the command was rejected.
     */
    public function discard(): void
    {
        $this->guardAgainstReentrancy();

        $this->pending = [];
    }

    private function guardAgainstReentrancy(): void
    {
        if ($this->dispatching) {
            throw new \LogicException(
                'DeferredDomainEventBus does not support reentrant publish()/dispatch()/discard() calls while a dispatch() is already in progress on this instance.'
            );
        }
    }
}
