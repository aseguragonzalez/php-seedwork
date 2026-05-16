<?php

declare(strict_types=1);

namespace SeedWork\Testing;

use SeedWork\Domain\AggregateRoot;

/**
 * In-memory implementation of {@see InMemoryRepositorySpy} for use in tests and examples.
 *
 * Keys aggregates by (string) cast of their id, so any id type that is stringable
 * (string, int, or object with __toString()) works out of the box.
 *
 * @template TId
 * @template T of AggregateRoot<TId>
 * @implements InMemoryRepositorySpy<TId, T>
 *
 * @see InMemoryRepositorySpy Spy interface adding all() and reset().
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

    public function reset(): void
    {
        $this->store = [];
    }

    private function key(mixed $id): string
    {
        /** @var string|int|\Stringable $id */
        return (string) $id;
    }
}
