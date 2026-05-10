<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use Psr\Container\ContainerInterface;
use SeedWork\Application\Command;
use SeedWork\Application\CommandBus;
use SeedWork\Application\CommandHandler;
use SeedWork\Application\Result;
use SeedWork\Application\ResultError;
use SeedWork\Domain\Exceptions\DomainException;

/**
 * PSR-11-based implementation of {@see CommandBus} that resolves handlers by command
 * class name using a container.
 *
 * @deprecated Use {@see RegistryCommandBus} instead. This class will be removed in a future release.
 *
 * @see RegistryCommandBus The preferred replacement without PSR-11 dependency.
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
     * @param class-string<Command> $commandType Command class name (FQCN).
     * @param string               $handlerId  Container service ID for the handler.
     */
    public function register(string $commandType, string $handlerId): void
    {
        $this->commandToHandler[$commandType] = $handlerId;
    }

    public function dispatch(Command $command): Result
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

        try {
            $handler->handle($command);
            return Result::ok();
        } catch (DomainException $e) {
            return Result::failed([new ResultError((string) $e->getCode(), $e->getMessage())]);
        }
    }
}
