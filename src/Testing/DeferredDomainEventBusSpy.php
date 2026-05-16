<?php

declare(strict_types=1);

namespace SeedWork\Testing;

use SeedWork\Domain\DomainEvent;
use SeedWork\Infrastructure\DeferredDomainEventBus;

/**
 * Test-only extension of {@see DeferredDomainEventBus} that implements {@see DomainEventBusSpy}.
 *
 * Adds pending() and reset() as test introspection on top of the production bus.
 * These methods are intentionally absent from the production class to prevent
 * consumers from depending on test-only lifecycle operations.
 *
 * Use this class in tests that need to assert on buffered events or reset the bus
 * between scenarios. Use {@see DeferredDomainEventBus} directly in production wiring.
 */
final class DeferredDomainEventBusSpy extends DeferredDomainEventBus implements DomainEventBusSpy
{
    /**
     * @return list<DomainEvent>
     */
    public function pending(): array
    {
        return array_values($this->pending);
    }

    public function reset(): void
    {
        $this->pending = [];
    }
}
