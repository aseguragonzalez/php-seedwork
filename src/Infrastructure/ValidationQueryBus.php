<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\Query;
use SeedWork\Application\QueryBus;
use SeedWork\Application\QueryResult;
use SeedWork\Application\QueryValidator;
use SeedWork\Application\ValidationErrors;

/**
 * QueryBus decorator that validates the query before delegating dispatch.
 *
 * Applies the injected {@see QueryValidator} first; throws {@see ValidationErrors}
 * on failure without reaching the inner bus. Stack this as the outermost decorator
 * so invalid queries are rejected before handler resolution.
 *
 * @see QueryBus        Application port this decorates.
 * @see QueryValidator  Port that provides the validation logic.
 * @see QueryBusBuilder Fluent builder to compose the QueryBus pipeline.
 */
final class ValidationQueryBus implements QueryBus
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly QueryValidator $validator,
    ) {
    }

    /**
     * Validates the query, then delegates to the inner bus.
     * Throws {@see ValidationErrors} without dispatching if validation fails.
     *
     * @return QueryResult The result from the inner bus.
     *
     * @throws ValidationErrors When the validator finds field-level failures.
     */
    public function ask(Query $query): QueryResult
    {
        $this->validator->validate($query);
        return $this->queryBus->ask($query);
    }
}
