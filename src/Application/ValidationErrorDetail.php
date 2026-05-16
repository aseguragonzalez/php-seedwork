<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Immutable DTO describing a single field-level validation failure.
 *
 * Used as elements of {@see ValidationErrors}; keeps error details serializable
 * (scalars only).
 *
 * @see ValidationErrors Exception that aggregates one or more ValidationErrorDetail instances.
 */
final readonly class ValidationErrorDetail
{
    public function __construct(
        public string $code,
        public string $message,
    ) {
    }
}
