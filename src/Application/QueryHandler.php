<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application use case for a read. Returns a {@see Maybe} container.
 *
 * @template-covariant T of Query
 * @see Query The query type this handler accepts.
 * @see Maybe The optional result container this handler returns.
 * @see QueryBus Dispatches queries to the appropriate handler.
 */
interface QueryHandler
{
    /**
     * @param Query $query The query to handle.
     * @return Maybe<mixed> The optional query result.
     */
    public function handle(Query $query): Maybe;
}
