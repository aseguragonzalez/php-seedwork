<?php

declare(strict_types=1);

namespace SeedWork\Testing;

use SeedWork\Infrastructure\TaskOutboxRecord;
use SeedWork\Infrastructure\TaskOutboxRepository;

/**
 * Spy extension of {@see TaskOutboxRepository} for use in tests.
 *
 * Adds introspection (all()) and reset() so tests can assert on stored records
 * and start each scenario with a clean slate.
 *
 * @see InMemoryTaskOutboxRepository Concrete spy implementation.
 */
interface TaskOutboxRepositorySpy extends TaskOutboxRepository
{
    /**
     * Returns all outbox records, regardless of status.
     *
     * @return array<TaskOutboxRecord>
     */
    public function all(): array;

    /**
     * Clears all records. Use in test tearDown / setUp.
     */
    public function reset(): void;
}
