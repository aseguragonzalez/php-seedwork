<?php

declare(strict_types=1);

namespace SeedWork\Testing;

use SeedWork\Infrastructure\DeferredDomainEventBus;

/**
 * Test-only extension of {@see DeferredDomainEventBus} that implements {@see DomainEventBusSpy}.
 *
 * Inherits pending() and reset() from the base class and formalises them as the
 * DomainEventBusSpy contract so test code can type-hint against the spy interface.
 *
 * Use this class in tests that need to assert on buffered events or reset the bus
 * between scenarios. Use {@see DeferredDomainEventBus} directly in production wiring.
 */
final class DeferredDomainEventBusSpy extends DeferredDomainEventBus implements DomainEventBusSpy
{
}
