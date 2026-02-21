<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application use case for a read. Implements one query type (T) and returns one
 * result type (R); invoked by {@see QueryBus}. Read-only (repositories, read
 * models); return {@see QueryResult} DTOs, not domain entities. Typically one
 * handler per Query class.
 *
 * @template T of Query
 * @template R of QueryResult
 * @see Query The query type this handler accepts.
 * @see QueryResult The result type this handler returns.
 * @see QueryBus Dispatches queries to the appropriate handler.
 */
interface QueryHandler
{
    /**
     * Returns the result for the given query.
     *
     * @param T $query The query to handle.
     *
     * @return R The query result DTO.
     */
    public function handle(Query $query): QueryResult;
}
