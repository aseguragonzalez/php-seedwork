<?php

declare(strict_types=1);

namespace SeedWork\Domain;

/**
 * Base type for Domain-Driven Design (DDD) Entities.
 *
 * An Entity is an object that is defined primarily by its identity rather than
 * by its attributes. Two entities are considered the same if they have the same
 * identity, even when their other properties differ. Identity persists across
 * time and different representations (e.g. after reload from persistence).
 *
 * The identity type TId is unconstrained: use any type your bounded context
 * prefers — a plain string, an int, a UUID, or a custom value object.
 *
 * @see https://domainlanguage.com/ddd/ Eric Evans, "Domain-Driven Design" (DDD reference).
 * @see https://martinfowler.com/bliki/EvansClassification.html Martin Fowler, P of EAA – Entity.
 *
 * @template TId
 */
abstract readonly class Entity
{
    /**
     * Constructs the entity with its identity.
     *
     * Identity is required and immutable; it is the sole basis for equality
     * between entities of the same type.
     *
     * @param TId $id The unique identity of this entity.
     */
    protected function __construct(public mixed $id)
    {
        $this->validate();
    }

    /**
     * Identity-based equality: two entities are equal iff they are of the same concrete
     * type and their IDs produce the same string representation.
     *
     * The class guard prevents cross-type false positives (e.g. Order#1 == Product#1).
     * String-cast strict comparison avoids PHP loose-equality quirks ("0e123" == 0).
     * TId must be stringable (string, int, or object with __toString()).
     *
     * @param Entity<TId> $other Another entity (typically of the same concrete type).
     * @return bool True if both entities have the same concrete type and identity.
     */
    public function equals(Entity $other): bool
    {
        /** @var string|int|\Stringable $thisId */
        $thisId = $this->id;
        /** @var string|int|\Stringable $otherId */
        $otherId = $other->id;

        return $this::class === $other::class
            && (string) $thisId === (string) $otherId;
    }

    /**
     * Validates the entity's invariants after construction.
     *
     * Override in subclasses to enforce domain rules that must always hold
     * (e.g. required fields, value ranges). Called automatically from the constructor.
     */
    abstract protected function validate(): void;
}
