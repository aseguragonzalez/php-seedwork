<?php

declare(strict_types=1);

namespace SeedWork\Application;

use SeedWork\Domain\DomainEvent;

/**
 * Application port for the domain event bus: publish, subscribe, dispatch and discard.
 *
 * Combines {@see DomainEventBusPublisher} (buffer events) and
 * {@see DomainEventBusSubscriber} (register handlers) with lifecycle operations:
 *
 * - dispatch() — run all buffered events through their handlers, then clear the buffer.
 * - discard()  — clear the buffer without dispatching (use on command failure).
 *
 * @see DomainEvent Events published through this bus.
 * @see DomainEventHandler Handlers registered via subscribe(); handle() is invoked on dispatch.
 */
interface DomainEventBus extends DomainEventBusPublisher, DomainEventBusSubscriber
{
    /**
     * Dispatches all buffered events to their registered handlers, then clears the buffer.
     */
    public function dispatch(): void;

    /**
     * Clears the buffer without dispatching. Use when the command was rejected.
     */
    public function discard(): void;
}
