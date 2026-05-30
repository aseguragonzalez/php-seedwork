<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\QueryBus;

/**
 * Fluent builder for composing a QueryBus pipeline from a RegistryQueryBus base
 * and optional decorator layers.
 *
 * Steps are accumulated and applied in reverse order during {@see build()}, so the
 * first step added becomes the outermost decorator (the first to receive a query).
 *
 * Example:
 * <code>
 * $registry = new RegistryQueryBus();
 * $registry->register(MyQuery::class, new MyQueryHandler());
 *
 * $bus = (new QueryBusBuilder($registry))
 *     ->use($myMiddleware)
 *     ->build();
 * </code>
 *
 * @see RegistryQueryBus Base bus; passed via constructor.
 */
final class QueryBusBuilder
{
    /** @var list<\Closure(QueryBus): QueryBus> */
    private array $steps = [];

    public function __construct(private readonly RegistryQueryBus $registry) {}

    public function registry(): RegistryQueryBus
    {
        return $this->registry;
    }

    /**
     * Adds a custom middleware step to the pipeline.
     *
     * @param \Closure(QueryBus): QueryBus $middleware
     */
    public function use(\Closure $middleware): self
    {
        $this->steps[] = $middleware;

        return $this;
    }

    /**
     * Builds the composed QueryBus pipeline.
     *
     * Steps are applied in reverse order: the first step added wraps the outermost layer.
     */
    public function build(): QueryBus
    {
        $bus = $this->registry;
        foreach (array_reverse($this->steps) as $step) {
            $bus = $step($bus);
        }

        return $bus;
    }
}
