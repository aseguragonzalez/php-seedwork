<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\Command;
use SeedWork\Application\CommandBus;
use SeedWork\Application\Result;

/**
 * CommandBus decorator that validates the command before delegating dispatch.
 *
 * Calls {@see Command::validate()} on the command; if validation fails,
 * the exception propagates to the caller without reaching the inner bus.
 * Stack this as the outermost decorator so invalid commands are rejected before
 * any transaction or domain-event-coordination layer.
 *
 * Recommended stacking order (outermost to innermost):
 *   ValidationCommandBus > TransactionalCommandBus > DomainEventCoordinatorCommandBus > RegistryCommandBus
 *
 * @see CommandBus        Application port this decorates.
 * @see Command::validate() Validation is driven by the command itself.
 * @see CommandBusBuilder Fluent builder to compose the CommandBus pipeline.
 */
final class ValidationCommandBus implements CommandBus
{
    public function __construct(
        private readonly CommandBus $inner,
    ) {
    }

    /**
     * @throws \SeedWork\Application\ValidationErrors
     */
    public function dispatch(Command $command): Result
    {
        $command->validate();
        return $this->inner->dispatch($command);
    }
}
