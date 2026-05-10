<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\Command;
use SeedWork\Application\CommandBus;
use SeedWork\Application\Result;

/**
 * CommandBus decorator that flushes the {@see DeferredDomainEventBus} after each
 * successful command, or clears it when the command returns {@see Result::failed()}.
 *
 * - Result::ok()   → flush() — dispatch buffered domain events.
 * - Result::failed() → clear() — discard events; domain rejected the operation.
 * - Throwable      → propagates; flush/clear is not called.
 *
 * @see CommandBus Application port.
 * @see DeferredDomainEventBus Event bus that buffers and flushes.
 */
final class DomainEventFlushCommandBus implements CommandBus
{
    public function __construct(
        private readonly CommandBus $inner,
        private readonly DeferredDomainEventBus $eventBus
    ) {
    }

    /**
     * Dispatches the command to the inner bus. Flushes the event bus on ok,
     * clears it on fail. Exceptions from the inner bus propagate unchanged.
     *
     * @param Command $command The command to dispatch.
     * @return Result The result from the inner bus.
     */
    public function dispatch(Command $command): Result
    {
        $result = $this->inner->dispatch($command);
        if ($result->isOk()) {
            $this->eventBus->flush();
        } else {
            $this->eventBus->clear();
        }
        return $result;
    }
}
