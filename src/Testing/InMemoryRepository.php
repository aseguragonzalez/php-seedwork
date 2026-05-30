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
 *
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
     * @return null|T
     */
    public function findById(mixed $id): ?AggregateRoot
    {
        return $this->store[$this->key($id)] ?? null;
    }

    public function deleteById(mixed $id): void
    {
        unset($this->store[$this->key($id)]);
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

    private function key(mixed $id): string
    {
        // @phpstan-ignore cast.string
        return (string) $id;
    }
}
