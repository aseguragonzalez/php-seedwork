<?php

declare(strict_types=1);

namespace Seedwork\Application;

/**
 * Application use case for a write. Implements one command type (T); invoked by
 * {@see CommandBus}. Depend on domain (e.g. {@see \Seedwork\Domain\Repository})
 * and {@see DomainEventBus}, not infrastructure; keep thin (orchestration only,
 * no business logic). Typically one handler per Command class.
 *
 * @template T of Command
 * @see Command The command type this handler accepts.
 * @see CommandBus Dispatches commands to the appropriate handler.
 * @see DomainEventBus Publish domain events collected from aggregates after persist.
 */
interface CommandHandler
{
    /**
     * Executes the use case for the given command. No return value; idempotency
     * is expressed by identity carried in the command.
     *
     * @param T $command The command to handle.
     */
    public function handle(Command $command): void;
}
