<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\QueryBus;

/**
 * Fluent builder for composing a QueryBus pipeline from a base bus and
 * optional decorator layers.
 *
 * The default base bus is {@see RegistryQueryBus}. Start with
 * {@see QueryBusBuilder::new()} (zero-arg) or {@see QueryBusBuilder::from()}
 * with a custom base, then chain decorators.
 *
 * Example:
 * <code>
 * $builder = QueryBusBuilder::new()->withValidation();
 * $builder->registry()->register(MyQuery::class, new MyQueryHandler());
 * $bus = $builder->build();
 * </code>
 *
 * @see RegistryQueryBus   Default base bus.
 * @see ValidationQueryBus Validation decorator.
 */
final class QueryBusBuilder
{
    private ?RegistryQueryBus $registryBus;
    private QueryBus $queryBus;

    private function __construct(?RegistryQueryBus $registryBus, QueryBus $queryBus)
    {
        $this->registryBus = $registryBus;
        $this->queryBus = $queryBus;
    }

    /**
     * Creates a builder with a {@see RegistryQueryBus} as the base bus.
     */
    public static function new(): self
    {
        $registry = new RegistryQueryBus();
        return new self($registry, $registry);
    }

    /**
     * Creates a builder with the given query bus as the base.
     *
     * If the provided bus is a {@see RegistryQueryBus}, it is also used as
     * the registry accessible via {@see registry()}. Otherwise {@see registry()}
     * will throw — use {@see QueryBusBuilder::new()} when you need handler registration.
     */
    public static function from(QueryBus $queryBus): self
    {
        $registry = $queryBus instanceof RegistryQueryBus ? $queryBus : null;
        return new self($registry, $queryBus);
    }

    /**
     * Returns the inner {@see RegistryQueryBus} for handler registration.
     *
     * Always returns the same registry instance regardless of how many decorators
     * have been added, when built with {@see new()} or {@see from(RegistryQueryBus)}.
     *
     * @throws \BadMethodCallException When built via {@see from()} with a non-RegistryQueryBus base.
     */
    public function registry(): RegistryQueryBus
    {
        if ($this->registryBus === null) {
            throw new \BadMethodCallException(
                'registry() is not available when using a custom non-registry base bus via from(). Use QueryBusBuilder::new() instead.'
            );
        }
        return $this->registryBus;
    }

    /**
     * Wraps the current bus in a {@see ValidationQueryBus}.
     */
    public function withValidation(): self
    {
        $this->queryBus = new ValidationQueryBus($this->queryBus);
        return $this;
    }

    public function build(): QueryBus
    {
        return $this->queryBus;
    }
}
