<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application use case for a read. Returns a {@see Maybe} container.
 *
 * The template is declared covariant so that a typed handler such as
 * QueryHandler<GetBankAccountStatusQuery> is assignable to QueryHandler<Query>
 * when registered in a query bus registry.
 *
 * @template-covariant T of Query
 *
 * @see Query The query type this handler accepts.
 * @see Maybe The optional result container this handler returns.
 * @see QueryBus Dispatches queries to the appropriate handler.
 */
interface QueryHandler
{
    /**
     * @param T $query the query to handle
     *
     * @return Maybe<mixed> the optional query result
     *
     * @phpstan-ignore generics.variance
     */
    public function handle(Query $query): Maybe;
}
