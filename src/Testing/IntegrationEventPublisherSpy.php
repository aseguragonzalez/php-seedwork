<?php

declare(strict_types=1);

namespace SeedWork\Testing;

use SeedWork\Application\IntegrationEvent;
use SeedWork\Application\IntegrationEventPublisher;

/**
 * Spy extension of {@see IntegrationEventPublisher} for use in tests.
 *
 * Adds introspection (published()) and reset() so tests can assert on published
 * events and start each scenario with a clean slate.
 *
 * @see InMemoryIntegrationEventPublisher Concrete implementation of this interface.
 */
interface IntegrationEventPublisherSpy extends IntegrationEventPublisher
{
    /**
     * Returns all events published so far.
     *
     * @return list<IntegrationEvent>
     */
    public function published(): array;

    /**
     * Clears the published list. Use in test tearDown / setUp.
     */
    public function reset(): void;
}
