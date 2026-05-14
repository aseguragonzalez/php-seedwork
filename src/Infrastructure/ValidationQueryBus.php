<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\Maybe;
use SeedWork\Application\Query;
use SeedWork\Application\QueryBus;
use SeedWork\Application\ValidationErrors;

/**
 * QueryBus decorator that validates the query before delegating to the inner bus.
 *
 * @see QueryBus        Application port this decorates.
 * @see Query::validate() Validation is driven by the query itself.
 */
final class ValidationQueryBus implements QueryBus
{
    public function __construct(
        private readonly QueryBus $inner,
    ) {
    }

    /**
     * @return Maybe<mixed>
     * @throws ValidationErrors
     */
    public function ask(Query $query): Maybe
    {
        $query->validate();
        return $this->inner->ask($query);
    }
}
