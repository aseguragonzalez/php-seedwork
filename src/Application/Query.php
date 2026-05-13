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
 * Subclasses must implement {@see validate()} to enforce field-level rules.
 * A validation decorator on the bus calls validate() before dispatching.
 *
 * @see QueryHandler Handlers that return a result for this query.
 * @see QueryBus Application port that dispatches queries to the right handler.
 */
abstract readonly class Query
{
    /**
     * Subclasses must call parent::__construct(); use a public constructor or
     * a named static factory for instantiation.
     */
    protected function __construct()
    {
    }

    /**
     * Validates the query's field-level rules.
     * Throw {@see ValidationErrors} when one or more validations fail.
     *
     * @throws ValidationErrors When one or more field-level validations fail.
     */
    abstract public function validate(): void;
}
