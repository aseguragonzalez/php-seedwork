<?php

declare(strict_types=1);

namespace SeedWork\Domain;

/**
 * Repository for an aggregate root type: load by id, persist, and delete by id.
 *
 * A Repository encapsulates persistence and retrieval for one kind of aggregate root. It presents
 * a collection-like interface to the domain (get by id, save, delete) and hides storage details
 * (database, cache, remote API). The domain uses repositories by identity and behaviour, not
 * by infrastructure; concrete implementations live in the application or infrastructure layer.
 *
 * The id type is unconstrained (mixed) to match the free generic TId on AggregateRoot.
 * Concrete implementations narrow it to the aggregate's actual id type.
 *
 * @see https://martinfowler.com/eaaCatalog/repository.html Repository (Fowler, P of EAA)
 * @see https://domainlanguage.com/ddd/reference/ Eric Evans, Domain-Driven Design
 *
 * @template T of AggregateRoot
 */
interface Repository
{
    /**
     * Persists the aggregate (create or update).
     *
     * @param T $aggregateRoot The aggregate to persist.
     */
    public function save(AggregateRoot $aggregateRoot): void;

    /**
     * Returns the aggregate for the given id, or null if not found.
     *
     * @param mixed $id The id of the aggregate to load.
     * @return T|null The aggregate, or null if not found.
     */
    public function findBy(mixed $id): ?AggregateRoot;

    /**
     * Removes the aggregate for the given id.
     *
     * @param mixed $id The id of the aggregate to delete.
     */
    public function deleteBy(mixed $id): void;
}
