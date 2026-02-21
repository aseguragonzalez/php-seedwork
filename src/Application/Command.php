<?php

declare(strict_types=1);

namespace Seedwork\Application;

/**
 * CQRS write-side request: immutable DTO carrying an intent (e.g. "deposit money").
 *
 * One command class per use case; dispatched via {@see CommandBus} to a single
 * {@see CommandHandler}. Rule: use only primitive attributes (scalars, array of
 * scalars); avoid domain types (entities, value objects) so the port stays
 * serializable and adapter-agnostic. Handlers translate primitives to domain
 * types when loading aggregates.
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
