<?php

declare(strict_types=1);

namespace Seedwork\Domain;

/**
 * Base type for entity identifiers.
 *
 * Extend this class per entity type (e.g. BankAccountId) and implement
 * {@see validate()} to enforce the ID format (e.g. UUID). Expose a static
 * named constructor such as fromString(string) or create() for consumers.
 */
abstract readonly class EntityId
{
    /**
     * @param string $value Raw identifier; must pass the subclass's validation.
     */
    protected function __construct(public string $value)
    {
        $this->validate();
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    /**
     * Use when comparing entity IDs (e.g. equality checks or repository lookups).
     */
    public function equals(EntityId $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Enforce the identifier format in your subclass (e.g. non-empty, UUID).
     */
    abstract protected function validate(): void;
}
