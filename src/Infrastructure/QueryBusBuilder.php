<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\QueryBus;
use SeedWork\Application\QueryValidator;

/**
 * Fluent builder for composing a QueryBus pipeline from a base bus and
 * optional decorator layers.
 *
 * Example:
 * <code>
 * $bus = QueryBusBuilder::from($containerBus)
 *     ->withValidation($validator)
 *     ->build();
 * </code>
 *
 * @see ContainerQueryBus  Default base bus.
 * @see ValidationQueryBus Validation decorator.
 */
final class QueryBusBuilder
{
    private function __construct(private QueryBus $queryBus)
    {
    }

    public static function from(QueryBus $queryBus): self
    {
        return new self($queryBus);
    }

    /**
     * Wraps the current bus in a {@see ValidationQueryBus}.
     */
    public function withValidation(QueryValidator $validator): self
    {
        $this->queryBus = new ValidationQueryBus($this->queryBus, $validator);
        return $this;
    }

    public function build(): QueryBus
    {
        return $this->queryBus;
    }
}
