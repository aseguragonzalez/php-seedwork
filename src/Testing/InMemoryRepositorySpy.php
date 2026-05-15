<?php

declare(strict_types=1);

namespace SeedWork\Testing;

use SeedWork\Domain\AggregateRoot;
use SeedWork\Domain\Repository;

/**
 * Spy extension of {@see Repository} for use in tests.
 *
 * Adds introspection (all()) and reset() so tests can assert on stored
 * aggregates and start each scenario with a clean slate.
 *
 * @template T of AggregateRoot
 * @extends Repository<T>
 *
 * @see InMemoryRepository Concrete implementation of this interface.
 */
interface InMemoryRepositorySpy extends Repository
{
    /**
     * Returns all aggregates currently in the store.
     *
     * @return list<T>
     */
    public function all(): array;

    /**
     * Clears all aggregates from the store. Use in test tearDown / setUp.
     */
    public function reset(): void;
}
