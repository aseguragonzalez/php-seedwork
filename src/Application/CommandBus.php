<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application port for dispatching commands. Decouples callers from handler
 * resolution and execution; typically one handler per Command class.
 *
 * Implementations resolve the handler for the command type (e.g. by class or
 * name) and call handle(). Infrastructure may add transactions, logging, or
 * middleware.
 *
 * @see Command Commands dispatched through this bus.
 * @see CommandHandler Handlers invoked by the bus for each command type.
 */
interface CommandBus
{
    /**
     * Dispatches the command to its handler. The handler is resolved by the
     * implementation (e.g. by command class name).
     *
     * @param Command $command The command to dispatch.
     */
    public function dispatch(Command $command): void;
}
