<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use Psr\Container\ContainerInterface;
use SeedWork\Application\Command;
use SeedWork\Application\CommandBus;
use SeedWork\Application\CommandHandler;

/**
 * PSR-11-based implementation of CommandBus that resolves handlers by command
 * class name using a container.
 *
 * Usage: (1) Construct with a ContainerInterface and optional initial map.
 * (2) Call register($commandFqcn, $handlerServiceId) for each command type.
 * (3) Call dispatch($command); the bus looks up the handler by $command::class,
 * gets it from the container, and invokes handle($command).
 *
 * Implementation: Handler resolution is by exact command FQCN; one handler per
 * command type. Handlers are resolved at dispatch time (lazy from container).
 * No middleware or transaction handling in this implementation.
 *
 * @throws \InvalidArgumentException When no handler is registered for the
 *         command class, or when the container returns a service that does not
 *         implement CommandHandler.
 */
final class ContainerCommandBus implements CommandBus
{
    /**
     * @param ContainerInterface       $container        PSR-11 container for resolving handlers.
     * @param array<string, string>    $commandToHandler Map of command class name (FQCN) to container
     *        service ID for the handler.
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private array $commandToHandler = []
    ) {
    }

    /**
     * Registers a command type with its handler. Re-registering the same command overwrites.
     *
     * @param class-string<Command> $commandType Command class name (FQCN).
     * @param string               $handlerId  Container service ID for the handler.
     */
    public function register(string $commandType, string $handlerId): void
    {
        $this->commandToHandler[$commandType] = $handlerId;
    }

    /**
     * Dispatches the command to its handler. Resolves handler by $command::class,
     * retrieves from container, asserts CommandHandler, then calls handle($command).
     *
     * @param Command $command The command to dispatch.
     *
     * @throws \InvalidArgumentException When no handler is registered for the command class,
     *         or when the container returns a service that does not implement CommandHandler.
     */
    public function dispatch(Command $command): void
    {
        $commandType = $command::class;
        if (!isset($this->commandToHandler[$commandType])) {
            throw new \InvalidArgumentException(
                sprintf('No handler registered for command %s.', $commandType)
            );
        }

        $handler = $this->container->get($this->commandToHandler[$commandType]);
        if (!$handler instanceof CommandHandler) {
            throw new \InvalidArgumentException(
                sprintf('Handler for command type %s is not a valid handler.', $commandType)
            );
        }
        $handler->handle($command);
    }
}
