<?php

declare(strict_types=1);

namespace Seedwork\Domain;

/**
 * Base type for Domain-Driven Design (DDD) Entities.
 *
 * An Entity is an object that is defined primarily by its identity rather than
 * by its attributes. Two entities are considered the same if they have the same
 * identity, even when their other properties differ. Identity persists across
 * time and different representations (e.g. after reload from persistence).
 *
 * @see EntityId Identity is represented by a subtype of EntityId (e.g. BankAccountId).
 * @see https://domainlanguage.com/ddd/ Eric Evans, "Domain-Driven Design" (DDD reference).
 * @see https://martinfowler.com/bliki/EvansClassification.html Martin Fowler, P of EAA – Entity.
 *
 * @template T of EntityId
 */
abstract readonly class Entity
{
    /**
     * Constructs the entity with its identity.
     *
     * Identity is required and immutable; it is the sole basis for equality
     * between entities of the same type.
     *
     * @param T $id The unique identity of this entity (subclass of EntityId).
     */
    protected function __construct(public EntityId $id)
    {
        $this->validate();
    }

    /**
     * Identity-based equality: two entities are equal iff they have the same ID.
     *
     * This reflects the DDD rule that entity equality is determined by identity,
     * not by comparing attributes.
     *
     * @param Entity<T> $other Another entity (typically of the same concrete type).
     * @return bool True if both entities have the same identity.
     */
    public function equals(Entity $other): bool
    {
        return $this->id->equals($other->id);
    }

    /**
     * Validates the entity's invariants after construction.
     *
     * Override in subclasses to enforce domain rules that must always hold
     * (e.g. required fields, value ranges). Called automatically from the constructor.
     */
    abstract protected function validate(): void;
}
