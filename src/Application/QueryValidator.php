<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application port for validating a query before dispatch.
 *
 * Implementations check query fields and throw {@see ValidationErrors} on
 * failure. Injected into {@see \SeedWork\Infrastructure\ValidationQueryBus}.
 *
 * @see Query            The query type to validate.
 * @see ValidationErrors Thrown when validation fails.
 */
interface QueryValidator
{
    /**
     * Validates the query. Throws {@see ValidationErrors} if validation fails.
     *
     * @throws ValidationErrors When one or more field-level validations fail.
     */
    public function validate(Query $query): void;
}
