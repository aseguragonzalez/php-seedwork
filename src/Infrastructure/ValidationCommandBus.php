<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\Command;
use SeedWork\Application\CommandBus;
use SeedWork\Application\Result;
use SeedWork\Application\ResultError;
use SeedWork\Application\ValidationError;
use SeedWork\Application\ValidationErrors;

/**
 * CommandBus decorator that validates the command before delegating dispatch.
 *
 * Calls {@see Command::validate()} on the command; if {@see ValidationErrors}
 * is thrown, converts it to {@see Result::failed()} without reaching the inner bus.
 * Stack this as the outermost decorator so invalid commands are rejected before
 * any transaction or event-flush layer.
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
     * Validates the command, then delegates to the inner bus.
     * Returns {@see Result::failed()} if validation fails; otherwise returns
     * the result from the inner bus.
     *
     * @param Command $command The command to dispatch.
     * @return Result The result of the dispatch.
     */
    public function dispatch(Command $command): Result
    {
        try {
            $command->validate();
        } catch (ValidationErrors $e) {
            $errors = array_values(array_map(
                fn (ValidationError $err) => new ResultError($err->field, $err->message),
                $e->errors
            ));
            assert($errors !== []);
            return Result::failed($errors);
        }
        return $this->inner->dispatch($command);
    }
}
