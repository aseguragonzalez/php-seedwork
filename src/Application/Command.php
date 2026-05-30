<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * CQRS write-side request: immutable DTO carrying an intent (e.g. "deposit money").
 *
 * One command class per use case; dispatched via {@see CommandBus} to a single
 * {@see CommandHandler}. Prefer attributes that are easy to serialize and keep
 * the port adapter-agnostic (scalars, arrays of scalars, simple value objects
 * such as IDs or money). Avoid passing full domain entities; handlers can load
 * and reconstruct rich domain objects when handling the command.
 *
 * Override {@see validate()} to enforce field-level rules; the base constructor
 * calls it at instantiation so an invalid Command cannot be constructed.
 * No bus decorator is needed — validation is guaranteed by the object lifecycle.
 *
 * @see CommandHandler Handlers that execute the use case for this command.
 * @see CommandBus Application port that dispatches commands to the right handler.
 */
abstract readonly class Command
{
    /**
     * Subclasses must call parent::__construct() so that validate() is invoked
     * at construction time. Use a public constructor or a named static factory.
     *
     * @throws ValidationErrors when one or more field-level validations fail
     */
    protected function __construct()
    {
        $this->validate();
    }

    /**
     * Override to enforce field-level rules; throw {@see ValidationErrors} on failure.
     * The base implementation is a no-op: subclasses that need validation must override.
     *
     * @throws ValidationErrors when one or more field-level validations fail
     */
    public function validate(): void {}
}
