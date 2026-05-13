<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\Command;
use SeedWork\Application\CommandBus;
use SeedWork\Application\DomainEventBus;
use SeedWork\Application\Result;

/**
 * CommandBus decorator that coordinates the {@see DomainEventBus} lifecycle after each command.
 *
 * - Result::ok()     → dispatch() — run buffered domain events through handlers.
 * - Result::failed() → discard()  — drop events; domain rejected the operation.
 * - Throwable        → discard() then rethrow — prevents stale events leaking into
 *                      a subsequent successful command when the bus is reused.
 *
 * Accepts the {@see DomainEventBus} interface (not the concrete class) so any
 * implementation can be injected (e.g. {@see DeferredDomainEventBus} in tests).
 *
 * @see CommandBus     Application port.
 * @see DomainEventBus Event bus whose lifecycle is coordinated by this decorator.
 */
final class DomainEventCoordinatorCommandBus implements CommandBus
{
    public function __construct(
        private readonly CommandBus $inner,
        private readonly DomainEventBus $eventBus
    ) {
    }

    /**
     * Dispatches the command to the inner bus. On ok, calls eventBus->dispatch();
     * on fail or exception, calls eventBus->discard(). Exceptions propagate after discard.
     *
     * @param Command $command The command to dispatch.
     * @return Result The result from the inner bus.
     */
    public function dispatch(Command $command): Result
    {
        try {
            $result = $this->inner->dispatch($command);
        } catch (\Throwable $e) {
            $this->eventBus->discard();
            throw $e;
        }
        if ($result->isOk()) {
            $this->eventBus->dispatch();
        } else {
            $this->eventBus->discard();
        }
        return $result;
    }
}
