<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Optional value container returned by {@see QueryHandler}s.
 *
 * Replaces the abstract QueryResult with a generic container that makes
 * the presence or absence of a value explicit. Use {@see just()} when the query
 * finds a result and {@see nothing()} when it does not.
 *
 * @template T
 *
 * @see QueryHandler Handlers return Maybe instead of QueryResult.
 * @see QueryBus Dispatches queries and returns Maybe.
 */
final class Maybe
{
    /** @param T|null $value */
    private function __construct(
        private readonly mixed $value
    ) {
    }

    /**
     * @param T $value
     * @return self<T>
     */
    public static function just(mixed $value): self
    {
        return new self($value);
    }

    /**
     * @return self<null>
     */
    public static function nothing(): self
    {
        return new self(null);
    }

    public function hasValue(): bool
    {
        return $this->value !== null;
    }

    /** @return T|null */
    public function value(): mixed
    {
        return $this->value;
    }
}
