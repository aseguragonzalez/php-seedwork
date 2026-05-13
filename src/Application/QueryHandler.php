<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application use case for a read. Returns a {@see Maybe} container.
 *
 * The template is declared covariant (`@template-covariant`) so that
 * `QueryHandler<GetBankAccountStatusQuery>` is assignable to `QueryHandler<Query>`
 * in registration registries. The `@phpstan-ignore generics.variance` on `handle()`
 * suppresses the expected covariance warning for the parameter position.
 *
 * @template-covariant T of Query
 * @see Query The query type this handler accepts.
 * @see Maybe The optional result container this handler returns.
 * @see QueryBus Dispatches queries to the appropriate handler.
 */
interface QueryHandler
{
    /**
     * @param T $query The query to handle.
     * @return Maybe<mixed> The optional query result.
     * @phpstan-ignore generics.variance
     */
    public function handle(Query $query): Maybe;
}
