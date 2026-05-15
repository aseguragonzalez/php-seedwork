<?php

declare(strict_types=1);

namespace SeedWork\Testing;

use SeedWork\Infrastructure\IntegrationEventOutboxRecord;
use SeedWork\Infrastructure\IntegrationEventOutboxRepository;

/**
 * Spy extension of {@see IntegrationEventOutboxRepository} for use in tests.
 *
 * Adds introspection (all()) and reset() so tests can assert on stored records
 * and start each scenario with a clean slate.
 *
 * @see InMemoryIntegrationEventOutboxRepository Concrete spy implementation.
 */
interface IntegrationEventOutboxRepositorySpy extends IntegrationEventOutboxRepository
{
    /**
     * Returns all outbox records, regardless of status.
     *
     * @return array<IntegrationEventOutboxRecord>
     */
    public function all(): array;

    /**
     * Clears all records. Use in test tearDown / setUp.
     */
    public function reset(): void;
}
