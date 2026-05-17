<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * CQRS read-side request: immutable DTO for a read use case.
 *
 * One query class per read use case; dispatched via {@see QueryBus} to a single
 * {@see QueryHandler}; no side effects. Prefer using primitive attributes
 * (scalars, arrays of scalars) to keep the port easily serializable and
 * adapter-agnostic; simple, serializable domain value objects may be used
 * when all involved adapters know how to handle them.
 *
 * Override {@see validate()} to enforce field-level rules; the base constructor
 * calls it at instantiation so an invalid Query cannot be constructed.
 * No bus decorator is needed — validation is guaranteed by the object lifecycle.
 *
 * @see QueryHandler Handlers that return a result for this query.
 * @see QueryBus Application port that dispatches queries to the right handler.
 */
abstract readonly class Query
{
    /**
     * Subclasses must call parent::__construct() so that validate() is invoked
     * at construction time. Use a public constructor or a named static factory.
     *
     * @throws ValidationErrors When one or more field-level validations fail.
     */
    protected function __construct()
    {
        $this->validate();
    }

    /**
     * Override to enforce field-level rules; throw {@see ValidationErrors} on failure.
     * The base implementation is a no-op: subclasses that need validation must override.
     *
     * @throws ValidationErrors When one or more field-level validations fail.
     */
    public function validate(): void
    {
    }
}
