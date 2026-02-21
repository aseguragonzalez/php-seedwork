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
 * @see CommandHandler Handlers that execute the use case for this command.
 * @see CommandBus Application port that dispatches commands to the right handler.
 */
abstract readonly class Command
{
    /**
     * Subclasses must call parent::__construct(); use a public constructor or
     * a named static factory for instantiation.
     */
    protected function __construct()
    {
    }
}
