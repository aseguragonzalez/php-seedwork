<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\Command;
use SeedWork\Application\CommandBus;
use SeedWork\Application\CommandHandler;
use SeedWork\Application\Result;
use SeedWork\Application\ResultError;

/**
 * Registry-based implementation of {@see CommandBus} without PSR-11.
 *
 * @see CommandBus Application port.
 * @see \DomainException Caught and wrapped in Result::failed().
 */
final class RegistryCommandBus implements CommandBus
{
    /** @var array<string, CommandHandler<Command>> */
    private array $handlers = [];

    /**
     * @param class-string<Command> $commandClass Command class name (FQCN).
     * @param CommandHandler<Command> $handler Handler instance.
     */
    public function register(string $commandClass, CommandHandler $handler): void
    {
        $this->handlers[$commandClass] = $handler;
    }

    public function dispatch(Command $command): Result
    {
        $handler = $this->handlers[$command::class]
            ?? throw new \LogicException('No handler for ' . $command::class);
        try {
            $handler->handle($command);
            return Result::ok();
        } catch (\DomainException $e) {
            $shortName = (new \ReflectionClass($e))->getShortName();
            $code = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName));
            return Result::failed([new ResultError($code, $e->getMessage())]);
        }
    }
}
