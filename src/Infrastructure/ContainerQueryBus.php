<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use Psr\Container\ContainerInterface;
use SeedWork\Application\Maybe;
use SeedWork\Application\Query;
use SeedWork\Application\QueryBus;
use SeedWork\Application\QueryHandler;

/**
 * PSR-11-based implementation of {@see QueryBus}.
 *
 * @deprecated Use {@see RegistryQueryBus} instead.
 */
final class ContainerQueryBus implements QueryBus
{
    /**
     * @param array<string, string> $queryToHandler
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private array $queryToHandler = []
    ) {
    }

    /**
     * @param class-string<Query> $queryType
     */
    public function register(string $queryType, string $handlerId): void
    {
        $this->queryToHandler[$queryType] = $handlerId;
    }

    /**
     * @return Maybe<mixed>
     */
    public function ask(Query $query): Maybe
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
