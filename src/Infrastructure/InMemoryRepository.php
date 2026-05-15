<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Domain\AggregateRoot;
use SeedWork\Domain\Repository;

/**
 * In-memory implementation of {@see Repository} for use in tests and examples.
 *
 * Keys aggregates by (string) cast of their id, so any id type that is stringable
 * (string, int, or object with __toString()) works out of the box.
 *
 * @template T of AggregateRoot
 * @implements InMemoryRepositorySpy<T>
 *
 * @see Repository              Domain port this implements.
 * @see InMemoryRepositorySpy   Spy interface adding all() and reset().
 */
class InMemoryRepository implements InMemoryRepositorySpy
{
    /** @var array<string, AggregateRoot> */
    protected array $store = [];

    /**
     * @param T $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot): void
    {
        $this->store[(string) $aggregateRoot->id] = $aggregateRoot;
    }

    /**
     * @param mixed $id
     * @return T|null
     */
    public function findById(mixed $id): ?AggregateRoot
    {
        /** @var T|null */
        return $this->store[(string) $id] ?? null;
    }

    /**
     * @param mixed $id
     */
    public function deleteById(mixed $id): void
    {
        unset($this->store[(string) $id]);
    }

    /**
     * @return list<T>
     */
    public function all(): array
    {
        return array_values($this->store);
    }

    public function reset(): void
    {
        $this->store = [];
    }
}
