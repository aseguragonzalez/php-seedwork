<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application port for dispatching commands. Decouples callers from handler
 * resolution and execution; typically one handler per Command class.
 *
 * Implementations resolve the handler for the command type (e.g. by class or
 * name) and call handle(). Infrastructure may add transactions, logging, or
 * middleware. Domain exceptions are caught by the base bus and returned as
 * {@see Result::failed()}; infrastructure exceptions propagate.
 *
 * @see Command Commands dispatched through this bus.
 * @see CommandHandler Handlers invoked by the bus for each command type.
 * @see Result The result of the command dispatch.
 */
interface CommandBus
{
    /**
     * Dispatches the command to its handler. The handler is resolved by the
     * implementation (e.g. by command class name). Returns {@see Result::ok()}
     * on success or {@see Result::failed()} when the domain rejects the operation.
     *
     * @param Command $command The command to dispatch.
     * @return Result The result of the dispatch.
     */
    public function dispatch(Command $command): Result;
}
