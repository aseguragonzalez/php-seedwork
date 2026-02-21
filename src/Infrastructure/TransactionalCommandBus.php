<?php

declare(strict_types=1);

namespace Seedwork\Infrastructure;

use Seedwork\Application\Command;
use Seedwork\Application\CommandBus;
use Seedwork\Domain\UnitOfWork;

/**
 * CommandBus that runs each dispatch inside a unit of work: creates a session,
 * dispatches to the decorated command bus, then commits on success or rolls back on throw.
 * Exceptions are rethrown after rollback.
 *
 * Usage: Wrap your CommandBus (e.g. ContainerCommandBus) with this and inject
 * a UnitOfWork implementation. When using both this and DomainEventFlushCommandBus,
 * put TransactionalCommandBus on the outside so the transaction wraps the
 * command and event flush (e.g. new TransactionalCommandBus(
 *     new DomainEventFlushCommandBus(containerBus, eventBus), unitOfWork)).
 *
 * @see CommandBus Application port.
 * @see UnitOfWork Transaction boundary contract.
 */
final class TransactionalCommandBus implements CommandBus
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly UnitOfWork $unitOfWork,
    ) {
    }

    /**
     * Dispatches the command within a unit-of-work session; commits on success,
     * rolls back on any throwable and rethrows.
     *
     * @param Command $command The command to dispatch.
     */
    public function dispatch(Command $command): void
    {
        $this->unitOfWork->createSession();

        try {
            $this->commandBus->dispatch($command);
            $this->unitOfWork->commit();
        } catch (\Throwable $e) {
            $this->unitOfWork->rollback();
            throw $e;
        }
    }
}
