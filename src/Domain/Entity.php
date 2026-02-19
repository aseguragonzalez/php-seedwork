<?php

declare(strict_types=1);

namespace Seedwork\Domain;

/**
 * @template T of EntityId
 */
abstract readonly class Entity
{
    /**
     * @param T $id
     */
    protected function __construct(public EntityId $id)
    {
    }

    /**
     * @param Entity<T> $other
     */
    public function equals(Entity $other): bool
    {
        return $this->id->equals($other->id);
    }
}
