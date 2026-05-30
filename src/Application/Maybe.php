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
    /** @param null|T $value */
    private function __construct(
        private readonly mixed $value,
        private readonly bool $hasValue
    ) {}

    /**
     * @template TVal
     *
     * @param TVal $value
     *
     * @return self<TVal>
     *
     * @throws \InvalidArgumentException When null is passed. Use {@see nothing()} instead.
     */
    public static function just(mixed $value): self
    {
        if (null === $value) {
            throw new \InvalidArgumentException('Maybe::just() cannot contain null. Use Maybe::nothing() instead.');
        }

        return new self($value, true);
    }

    /**
     * @return self<never>
     */
    public static function nothing(): self
    {
        // @phpstan-ignore return.type
        return new self(null, false);
    }

    public function hasValue(): bool
    {
        return $this->hasValue;
    }

    /**
     * @return T
     *
     * @throws \LogicException when called on a nothing value; check {@see hasValue()} first
     */
    public function value(): mixed
    {
        if (!$this->hasValue) {
            throw new \LogicException('Cannot call value() on Maybe::nothing(). Check hasValue() first.');
        }

        assert(null !== $this->value);

        return $this->value;
    }
}
