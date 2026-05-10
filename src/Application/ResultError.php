<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Immutable DTO describing a single error in a failed {@see Result}.
 *
 * Keeps error details serializable (scalars only).
 *
 * @see Result The result that may contain zero or more ResultError instances.
 */
final readonly class ResultError
{
    public function __construct(
        public string $code,
        public string $message
    ) {
    }
}
