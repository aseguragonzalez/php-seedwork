<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application port for validating a command before dispatch.
 *
 * Implementations check command fields and throw {@see ValidationErrors} on
 * failure. Injected into {@see \SeedWork\Infrastructure\ValidationCommandBus}.
 *
 * @see Command          The command type to validate.
 * @see ValidationErrors Thrown when validation fails.
 */
interface CommandValidator
{
    /**
     * Validates the command. Throws {@see ValidationErrors} if validation fails.
     *
     * @throws ValidationErrors When one or more field-level validations fail.
     */
    public function validate(Command $command): void;
}
