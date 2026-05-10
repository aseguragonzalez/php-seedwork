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
 * </code>
 *
 * @see RegistryQueryBus   Default base bus.
 * @see ValidationQueryBus Validation decorator.
 */
final class QueryBusBuilder
{
    private function __construct(private QueryBus $queryBus)
    {
    }

    /**
     * Creates a builder with a {@see RegistryQueryBus} as the base bus.
     */
    public static function new(): self
    {
        return new self(new RegistryQueryBus());
    }

    /**
     * Creates a builder with the given query bus as the base.
     */
    public static function from(QueryBus $queryBus): self
    {
        return new self($queryBus);
    }

    /**
     * Returns the inner {@see RegistryQueryBus} for handler registration.
     * Only available when the base bus is a RegistryQueryBus.
     *
     * @throws \LogicException When the base bus is not a RegistryQueryBus.
     */
    public function registry(): RegistryQueryBus
    {
        if (!$this->queryBus instanceof RegistryQueryBus) {
            throw new \LogicException('Base bus is not a RegistryQueryBus.');
        }
        return $this->queryBus;
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
