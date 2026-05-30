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
 * TId is the identity type (e.g. string, int, or a value object); T is the aggregate type.
 * Concrete implementations specify both: @extends Repository<MyId, MyAggregate>.
 *
 * @see https://martinfowler.com/eaaCatalog/repository.html Repository (Fowler, P of EAA)
 * @see https://domainlanguage.com/ddd/reference/ Eric Evans, Domain-Driven Design
 *
 * @template TId
 * @template T of AggregateRoot<TId>
 */
interface Repository
{
    /**
     * Persists the aggregate (create or update).
     *
     * @param T $aggregateRoot the aggregate to persist
     */
    public function save(AggregateRoot $aggregateRoot): void;

    /**
     * Returns the aggregate for the given id, or null if not found.
     *
     * @param TId $id the id of the aggregate to load
     *
     * @return null|T the aggregate, or null if not found
     */
    public function findById(mixed $id): ?AggregateRoot;

    /**
     * Removes the aggregate for the given id.
     *
     * @param TId $id the id of the aggregate to delete
     */
    public function deleteById(mixed $id): void;
}
