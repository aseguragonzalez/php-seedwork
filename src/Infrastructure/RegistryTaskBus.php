<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\BackgroundTask;
use SeedWork\Application\TaskBus;
use SeedWork\Application\TaskHandler;

/**
 * Registry-based implementation of {@see TaskBus} that dispatches background tasks
 * to registered handler instances by task type string.
 *
 * @see TaskBus     Application port this implements.
 * @see TaskHandler Handler instances invoked per task type.
 */
final class RegistryTaskBus implements TaskBus
{
    /** @var array<string, TaskHandler> */
    private array $handlers = [];

    /**
     * Registers a handler for the given task type.
     *
     * @param string $taskType The task type identifier (e.g. 'domain.action_name').
     * @param TaskHandler $handler Handler instance to invoke.
     */
    public function register(string $taskType, TaskHandler $handler): void
    {
        $this->handlers[$taskType] = $handler;
    }

    /**
     * Dispatches the task to its registered handler.
     *
     * @param BackgroundTask $task The task to dispatch.
     * @throws \RuntimeException When no handler is registered for the task type.
     */
    public function dispatch(BackgroundTask $task): void
    {
        $handler = $this->handlers[$task->type]
            ?? throw new \RuntimeException("No handler for task type: {$task->type}");
        $handler->handle($task);
    }
}
