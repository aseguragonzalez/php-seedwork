<?php

declare(strict_types=1);

namespace SeedWork\Domain;

/**
 * Base implementation of the Value Object pattern.
 *
 * A Value Object is an immutable object defined by its attributes rather than identity.
 * Two value objects are considered equal when all their attributes are equal. They have
 * no lifecycle and no identity; they are interchangeable when their values match.
 *
 * Subclasses must:
 * - Be immutable (this base is {@see readonly}); avoid mutable properties.
 * - Implement {@see equals()} to compare by value (all significant attributes).
 * - Implement {@see validate()} to enforce invariants; it is called from the constructor.
 *
 * Construction is protected so creation goes through public constructors in subclasses
 * or static factories in subclasses, ensuring validation always runs.
 *
 * @see https://martinfowler.com/bliki/ValueObject.html Martin Fowler - ValueObject
 * @see https://domainlanguage.com/ddd/reference/ Eric Evans' DDD Reference - Value Objects
 */
abstract readonly class ValueObject
{
    /**
     * Ensures the value object is created in a valid state.
     *
     * Subclasses must call this from their (protected) constructors or named constructors.
     * Validation runs automatically and must throw on invalid data.
     */
    protected function __construct()
    {
        $this->validate();
    }

    /**
     * Compares this value object to another by value (attributes), not by identity.
     *
     * @param ValueObject $other Another value object of the same (or compatible) type.
     * @return bool True if all significant attributes are equal.
     */
    abstract public function equals(ValueObject $other): bool;

    /**
     * Validates the value object's invariants.
     *
     * Called from the constructor. Must throw (e.g. InvalidArgumentException) when
     * the object would be in an invalid state.
     *
     * @throws \InvalidArgumentException When validation fails.
     */
    abstract protected function validate(): void;
}
