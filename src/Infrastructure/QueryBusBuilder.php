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
 * $bus = QueryBusBuilder::new()
 *     ->withValidation()
 *     ->build();
 * $bus->registry()->register(MyQuery::class, new MyQueryHandler());
 * </code>
 *
 * @see RegistryQueryBus   Default base bus.
 * @see ValidationQueryBus Validation decorator.
 */
final class QueryBusBuilder
{
    private readonly RegistryQueryBus $registryBus;
    private QueryBus $queryBus;

    public function __construct()
    {
        $this->registryBus = new RegistryQueryBus();
        $this->queryBus = $this->registryBus;
    }

    /**
     * Creates a builder with a {@see RegistryQueryBus} as the base bus.
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * Creates a builder with the given query bus as the base.
     * Note: {@see registry()} is not available when using a custom base.
     *
     * @deprecated Use {@see QueryBusBuilder::new()} for the default RegistryQueryBus base.
     */
    public static function from(QueryBus $queryBus): self
    {
        $builder = new self();
        $builder->queryBus = $queryBus;
        return $builder;
    }

    /**
     * Returns the inner {@see RegistryQueryBus} for handler registration.
     *
     * When built with {@see new()}, always returns the same registry instance
     * regardless of how many decorators have been added. When built with
     * {@see from()}, returns the internal registry (not the custom base).
     */
    public function registry(): RegistryQueryBus
    {
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
