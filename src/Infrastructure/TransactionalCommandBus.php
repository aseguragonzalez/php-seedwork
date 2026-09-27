<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\Command;
use SeedWork\Application\CommandBus;
use SeedWork\Application\Result;
use SeedWork\Domain\UnitOfWork;

/**
 * CommandBus decorator that runs each dispatch inside a unit of work: creates a
 * session, dispatches to the decorated command bus, then commits only when the
 * command succeeded. A failed result or an exception rolls the session back, so
 * a command that did not succeed never leaves partial writes behind.
 *
 * - Result::ok()     → commit. If commit() throws, rollback and rethrow.
 * - Result::failed() → rollback; the failed Result is returned (not thrown).
 * - Throwable        → rollback and rethrow.
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
     * Dispatches the command within a unit-of-work session; commits on
     * Result::ok(), rolls back on Result::failed(), rolls back and rethrows on
     * any throwable (including one raised by commit()).
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
        } catch (\Throwable $e) {
            $this->unitOfWork->rollback();

            throw $e;
        }

        if ($result->isFailed()) {
            $this->unitOfWork->rollback();

            return $result;
        }

        try {
            $this->unitOfWork->commit();
        } catch (\Throwable $e) {
            $this->unitOfWork->rollback();

            throw $e;
        }

        return $result;
    }
}
