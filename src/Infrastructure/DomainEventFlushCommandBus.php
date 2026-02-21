<?php

declare(strict_types=1);

namespace Seedwork\Infrastructure;

use Seedwork\Application\Command;
use Seedwork\Application\CommandBus;

/**
 * CommandBus decorator that flushes the DeferredDomainEventBus after each
 * command is executed successfully. Ensures domain events published during
 * command handling are dispatched to subscribers once the command completes.
 * If the command throws, flush() is not called and the exception propagates.
 *
 * Usage: Wrap your real CommandBus (e.g. ContainerCommandBus) with this
 * decorator and inject the same DeferredDomainEventBus used by your handlers.
 *
 * @see CommandBus Application port.
 * @see DeferredDomainEventBus Event bus that buffers and flushes.
 */
final class DomainEventFlushCommandBus implements CommandBus
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly DeferredDomainEventBus $domainEventBus
    ) {
    }

    /**
     * Dispatches the command to the inner bus, then flushes the event bus
     * only when the command completes without throwing.
     *
     * @param Command $command The command to dispatch.
     */
    public function dispatch(Command $command): void
    {
        $this->commandBus->dispatch($command);
        $this->domainEventBus->flush();
    }
}
