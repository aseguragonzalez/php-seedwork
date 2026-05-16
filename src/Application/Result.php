<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Represents the outcome of a command dispatch.
 *
 * A {@see Result} is either successful (ok) or failed (with one or more errors).
 * Command handlers return void and domain logic throws {@see \DomainException};
 * the bus layer converts these into Result instances so callers have a typed,
 * exception-free way to check outcomes.
 *
 * @see ResultError A single error detail within a failed result.
 * @see CommandBus CommandBus.dispatch() returns Result.
 */
final class Result
{
    /** @param array<ResultError> $errors */
    private function __construct(
        private readonly bool $success,
        private readonly array $errors = []
    ) {
    }

    public static function ok(): self
    {
        return new self(true);
    }

    /** @param array<ResultError> $errors One or more errors describing the failure. */
    public static function failed(array $errors): self
    {
        if (count($errors) === 0) {
            throw new \InvalidArgumentException('Result::failed() requires at least one error.');
        }

        return new self(false, $errors);
    }

    public function isOk(): bool
    {
        return $this->success;
    }

    public function isFail(): bool
    {
        return !$this->success;
    }

    /** @return array<ResultError> */
    public function errors(): array
    {
        return $this->errors;
    }
}
