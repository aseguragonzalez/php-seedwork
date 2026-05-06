<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\Command;
use SeedWork\Application\CommandBus;
use SeedWork\Application\CommandValidator;
use SeedWork\Application\ValidationErrors;

/**
 * CommandBus decorator that validates the command before delegating dispatch.
 *
 * Applies the injected {@see CommandValidator} first; throws {@see ValidationErrors}
 * on failure without reaching the inner bus. Stack this as the outermost decorator
 * so invalid commands are rejected before any transaction or event-flush layer.
 *
 * Recommended stacking order (outermost to innermost):
 *   ValidationCommandBus > TransactionalCommandBus > DomainEventFlushCommandBus > ContainerCommandBus
 *
 * @see CommandBus        Application port this decorates.
 * @see CommandValidator  Port that provides the validation logic.
 * @see CommandBusBuilder Fluent builder to compose the CommandBus pipeline.
 */
final class ValidationCommandBus implements CommandBus
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly CommandValidator $validator,
    ) {
    }

    /**
     * Validates the command, then delegates to the inner bus.
     * Throws {@see ValidationErrors} without dispatching if validation fails.
     *
     * @throws ValidationErrors When the validator finds field-level failures.
     */
    public function dispatch(Command $command): void
    {
        $this->validator->validate($command);
        $this->commandBus->dispatch($command);
    }
}
