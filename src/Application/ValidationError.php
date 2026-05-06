<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Immutable DTO describing a single field-level validation failure.
 *
 * Used as elements of {@see ValidationErrors}; keeps error details serializable
 * (scalars only).
 *
 * @see ValidationErrors Exception that aggregates one or more ValidationError instances.
 */
final readonly class ValidationError
{
    public function __construct(
        public string $field,
        public string $message,
    ) {
    }
}
