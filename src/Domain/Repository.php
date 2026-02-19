<?php

declare(strict_types=1);

namespace Seedwork\Domain;

/**
 * @template T of AggregateRoot
 */
interface Repository
{
    /**
     * @param T $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot): void;

    /**
     * @param EntityId $id
     * @return T
     */
    public function getById(EntityId $id): AggregateRoot;

    /**
     * @param EntityId $id
     */
    public function deleteById(EntityId $id): void;
}
