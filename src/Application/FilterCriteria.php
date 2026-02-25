<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Immutable base for a single filter criterion used by {@see QueryRepository::filter}.
 *
 * Concrete criteria live in application or bounded context and implement
 * {@see validate()} to enforce allowed fields and value shape (e.g. between
 * requires an array of two elements). Value is a scalar for single-value
 * operators or an array for IN and BETWEEN.
 *
 * @template TValue
 * @see QueryRepository::filter
 * @see FilterOperator
 */
abstract readonly class FilterCriteria
{
    /**
     * @param TValue $value
     */
    protected function __construct(
        public string $field,
        public FilterOperator $operator,
        /** @var TValue */
        public mixed $value,
    ) {
        $this->checkOperator();
        $this->validate();
    }

    protected function checkOperator(): void
    {
        if ($this->operator === FilterOperator::BETWEEN && (!is_array($this->value) || count($this->value) < 2)) {
            throw new \InvalidArgumentException('Operator BETWEEN requires value to be an array of two elements');
        }

        if ($this->operator === FilterOperator::IN && !is_array($this->value)) {
            throw new \InvalidArgumentException('Operator IN requires value to be an array');
        }
    }

    /**
     * Enforce invariants (allowed fields, value shape for operator). Throw on
     * invalid state.
     */
    abstract protected function validate(): void;
}
