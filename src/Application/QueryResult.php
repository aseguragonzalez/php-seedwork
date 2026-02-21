<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Immutable DTO returned by {@see QueryHandler}s; used at the application/port
 * boundary. Prefer primitive attributes (scalars, arrays of scalars or simple
 * structures); avoid domain entities or complex domain types so responses stay
 * serializable. Subclasses typically declare public readonly properties.
 *
 * @see QueryHandler Handlers that build and return this result.
 * @see Query The query that produced this result.
 */
abstract readonly class QueryResult
{
    /**
     * Subclasses must call parent::__construct().
     */
    protected function __construct()
    {
    }
}
