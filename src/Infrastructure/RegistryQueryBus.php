<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\Maybe;
use SeedWork\Application\Query;
use SeedWork\Application\QueryBus;
use SeedWork\Application\QueryHandler;

/**
 * Registry-based implementation of {@see QueryBus} without PSR-11.
 *
 * @see QueryBus Application port.
 */
final class RegistryQueryBus implements QueryBus
{
    /** @var array<string, QueryHandler<Query>> */
    private array $handlers = [];

    /**
     * Registers a query type with its handler instance. Re-registering overwrites.
     *
     * @param class-string<Query> $queryClass query class name (FQCN)
     * @param QueryHandler<Query> $handler    handler instance to invoke for this query
     */
    public function register(string $queryClass, QueryHandler $handler): void
    {
        $this->handlers[$queryClass] = $handler;
    }

    /**
     * @return Maybe<mixed>
     */
    public function ask(Query $query): Maybe
    {
        $handler = $this->handlers[$query::class]
            ?? throw new \LogicException('No handler for '.$query::class);

        return $handler->handle($query);
    }
}
