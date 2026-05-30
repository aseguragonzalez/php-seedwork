<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\Command;
use SeedWork\Application\CommandBus;
use SeedWork\Application\Result;
use SeedWork\Domain\UnitOfWork;

/**
 * CommandBus decorator that runs each dispatch inside a unit of work: creates a
 * session, dispatches to the decorated command bus, then commits on success
 * (including Result::failed — the domain rejected it cleanly) or rolls back on
 * infrastructure exceptions.
 *
 * - Result::ok() or Result::failed() → commit (no infrastructure error).
 * - Throwable                        → rollback and rethrow.
 *
 * Recommended stacking (outer → inner):
 *   TransactionalCommandBus > DomainEventCoordinatorCommandBus > RegistryCommandBus
 *
 * @see CommandBus Application port.
 * @see UnitOfWork Transaction boundary contract.
 */
final class TransactionalCommandBus implements CommandBus
{
    public function __construct(
        private readonly CommandBus $inner,
        private readonly UnitOfWork $unitOfWork,
    ) {}

    /**
     * Dispatches the command within a unit-of-work session; commits on result
     * (ok or failed), rolls back and rethrows on any throwable.
     *
     * @param Command $command the command to dispatch
     *
     * @return Result the result from the inner bus
     */
    public function dispatch(Command $command): Result
    {
        $this->unitOfWork->createSession();

        try {
            $result = $this->inner->dispatch($command);
            $this->unitOfWork->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->unitOfWork->rollback();

            throw $e;
        }
    }
}
