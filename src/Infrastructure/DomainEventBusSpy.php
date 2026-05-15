<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\DomainEventBus;
use SeedWork\Domain\DomainEvent;

/**
 * Spy extension of {@see DomainEventBus} for use in tests.
 *
 * Adds introspection (pending()) and reset() so tests can assert on buffered
 * events and start each scenario with a clean slate.
 *
 * reset() differs from discard(): discard() is a production lifecycle call
 * (command rejected — drop events); reset() is for test setup (wipe pending
 * buffer between scenarios, keeping handler subscriptions intact).
 *
 * @see DeferredDomainEventBus Concrete implementation of this interface.
 */
interface DomainEventBusSpy extends DomainEventBus
{
    /**
     * Returns all domain events currently buffered but not yet dispatched.
     *
     * @return list<DomainEvent>
     */
    public function pending(): array;

    /**
     * Clears the pending buffer without dispatching. Use in test setUp / tearDown.
     * Handler subscriptions are preserved.
     */
    public function reset(): void;
}
