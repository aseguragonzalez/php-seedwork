<?php

declare(strict_types=1);

namespace Seedwork\Domain;

/**
 * Base type for domain event identifiers.
 *
 * Extend this class per event type (e.g. BankAccountEventId) and implement
 * {@see validate()} to enforce the ID format (e.g. UUID). Expose a static
 * named constructor such as fromString(string) or create() for consumers.
 */
abstract readonly class EventId
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
        return $this->value;
    }

    /**
     * Use when comparing event IDs (e.g. deduplication or equality checks).
     */
    public function equals(EventId $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Enforce the identifier format in your subclass (e.g. non-empty, UUID).
     */
    abstract protected function validate(): void;
}
