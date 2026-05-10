<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application port for dispatching queries and returning results.
 *
 * @see Query Queries dispatched through this bus.
 * @see QueryHandler Handlers invoked by the bus for each query type.
 * @see Maybe Container for the optional result.
 */
interface QueryBus
{
    /**
     * @param Query $query The query to dispatch.
     * @return Maybe<mixed> The result from the query handler.
     */
    public function ask(Query $query): Maybe;
}
