<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Exception thrown when one or more field-level validation rules fail.
 *
 * Thrown at the application boundary (e.g. by a {@see CommandValidator} or
 * {@see QueryValidator}) before a command or query reaches its handler.
 * Callers inspect {@see $errors} to produce user-facing messages.
 *
 * @see ValidationError  A single field-level error detail.
 * @see CommandValidator Application port that validates commands.
 * @see QueryValidator   Application port that validates queries.
 */
final class ValidationErrors extends \Exception
{
    /**
     * @param array<ValidationError> $errors One or more field-level validation failures.
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Validation errors');
    }
}
