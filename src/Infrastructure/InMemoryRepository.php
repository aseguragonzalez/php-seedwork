<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Domain\AggregateRoot;
use SeedWork\Domain\EntityId;
use SeedWork\Domain\Repository;

/**
 * In-memory implementation of {@see Repository} for use in tests and examples.
 *
 * @template T of AggregateRoot
 * @implements Repository<T>
 *
 * @see Repository Domain port this implements.
 */
class InMemoryRepository implements Repository
{
    /** @var array<string, AggregateRoot<EntityId>> */
    protected array $store = [];

    /**
     * @param T $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot): void
    {
        $this->store[$aggregateRoot->id->value] = $aggregateRoot;
    }

    /**
     * @param EntityId $id
     * @return T|null
     */
    public function findBy(EntityId $id): ?AggregateRoot
    {
        /** @var T|null */
        return $this->store[$id->value] ?? null;
    }

    /**
     * @param EntityId $id
     */
    public function deleteBy(EntityId $id): void
    {
        unset($this->store[$id->value]);
    }
}
