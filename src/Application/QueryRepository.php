<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Port for reading projections (read-model DTOs) from the system.
 *
 * Used by query handlers to retrieve data in a projection form without loading
 * domain aggregates. Implementations live in infrastructure (e.g. DB, search).
 * No business logic; only retrieval.
 *
 * @template T of object
 *
 * @see FilterCriteria Used for filter() criteria.
 */
interface QueryRepository
{
    /**
     * Returns the projection for the given id, or null if not found.
     *
     * @return T|null
     */
    public function getById(string $id): ?object;

    /**
     * Returns a slice of projections matching the given criteria (offset and
     * limit). No count or total is returned.
     *
     * @param array<FilterCriteria<mixed>> $filters
     *
     * @return array<T>
     */
    public function filter(int $offset, int $limit, array $filters): array;
}
