<?php

declare(strict_types=1);

namespace Seedwork\Application;

/**
 * CQRS read-side request: immutable DTO for a read use case.
 *
 * One query class per read use case; dispatched via {@see QueryBus} to a single
 * {@see QueryHandler}; no side effects. Rule: use only primitive attributes
 * (scalars, array of scalars); avoid domain types so the port stays
 * serializable and adapter-agnostic.
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
}
