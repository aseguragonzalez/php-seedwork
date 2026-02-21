<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use Psr\Container\ContainerInterface;
use SeedWork\Application\Query;
use SeedWork\Application\QueryBus;
use SeedWork\Application\QueryHandler;
use SeedWork\Application\QueryResult;

/**
 * PSR-11-based implementation of QueryBus that resolves query handlers by query
 * class name via a container.
 *
 * Usage: (1) Construct with ContainerInterface and optional query-to-handler map.
 * (2) Call register($queryFqcn, $handlerServiceId) per query type.
 * (3) Call ask($query) to get a QueryResult; handler is resolved by $query::class.
 *
 * Implementation: One handler per query type; resolution at ask-time; read-only
 * (no domain state change). Callers can narrow return type in app code (e.g. to a
 * specific result DTO).
 *
 * @throws \InvalidArgumentException When no handler is registered for the query
 *         class, or when the container returns a non-QueryHandler.
 */
final class ContainerQueryBus implements QueryBus
{
    /**
     * @param ContainerInterface    $container      PSR-11 container for resolving handlers.
     * @param array<string, string> $queryToHandler Map of query class name (FQCN) to container
     *        service ID for the handler.
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private array $queryToHandler = []
    ) {
    }

    /**
     * Registers a query type with its handler. Re-registering the same query overwrites.
     *
     * @param class-string<Query> $queryType Query class name (FQCN).
     * @param string              $handlerId Container service ID for the handler.
     */
    public function register(string $queryType, string $handlerId): void
    {
        $this->queryToHandler[$queryType] = $handlerId;
    }

    /**
     * Dispatches the query to its handler and returns the result. Resolves handler
     * by $query::class, gets from container, asserts QueryHandler, returns handle($query).
     *
     * @param Query $query The query to dispatch.
     *
     * @return QueryResult The result from the query handler.
     *
     * @throws \InvalidArgumentException When no handler is registered for the query class,
     *         or when the container returns a service that does not implement QueryHandler.
     */
    public function ask(Query $query): QueryResult
    {
        $queryType = $query::class;
        if (!isset($this->queryToHandler[$queryType])) {
            throw new \InvalidArgumentException(
                sprintf('No handler registered for query %s.', $queryType)
            );
        }

        $handler = $this->container->get($this->queryToHandler[$queryType]);
        if (!$handler instanceof QueryHandler) {
            throw new \InvalidArgumentException(
                sprintf('Handler for query type %s is not a valid handler.', $queryType)
            );
        }
        return $handler->handle($query);
    }
}
