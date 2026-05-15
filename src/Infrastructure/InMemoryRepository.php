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
    /** @var array<string, T> */
    protected array $store = [];

    /**
     * @param T $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot): void
    {
        $this->store[$this->key($aggregateRoot->id)] = $aggregateRoot;
    }

    /**
     * @param mixed $id
     * @return T|null
     */
    public function findById(mixed $id): ?AggregateRoot
    {
        /** @var T|null */
        return $this->store[$this->key($id)] ?? null;
    }

    /**
     * @param mixed $id
     */
    public function deleteById(mixed $id): void
    {
        unset($this->store[$this->key($id)]);
    }

    /**
     * @return list<T>
     */
    public function all(): array
    {
        /** @var list<T> */
        return array_values($this->store);
    }

    private function key(mixed $id): string
    {
        /** @var string|int|\Stringable $id */
        return (string) $id;
    }

    public function reset(): void
    {
        $this->store = [];
    }
}
