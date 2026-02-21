<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application port for dispatching queries and returning results. Decouples
 * callers from handler resolution; typically one handler per Query class.
 * Read-only; no domain changes.
 *
 * Implementations resolve the handler for the query type and return its
 * QueryResult. Callers may narrow the return type in app code or docblocks
 * (e.g. BankAccountStatusResult for GetBankAccountStatusQuery).
 *
 * @see Query Queries dispatched through this bus.
 * @see QueryHandler Handlers invoked by the bus for each query type.
 * @see QueryResult DTOs returned by query handlers.
 */
interface QueryBus
{
    /**
     * Dispatches the query to its handler and returns the result. The handler
     * is resolved by the implementation (e.g. by query class name).
     *
     * @param Query $query The query to dispatch.
     *
     * @return QueryResult The result from the query handler.
     */
    public function ask(Query $query): QueryResult;
}
