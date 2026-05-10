<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application port for dispatching background tasks to their handlers.
 *
 * Decouples the caller from handler resolution. Implementations register
 * handlers by task type and dispatch tasks to the appropriate handler.
 *
 * @see BackgroundTask Tasks dispatched through this bus.
 * @see TaskHandler    Handlers invoked by the bus for each task type.
 */
interface TaskBus
{
    /**
     * Registers a handler for the given task type.
     *
     * @param string $taskType The task type identifier (e.g. 'domain.action_name').
     * @param TaskHandler $handler Handler instance to invoke.
     */
    public function register(string $taskType, TaskHandler $handler): void;

    /**
     * Dispatches the task to its registered handler.
     *
     * @param BackgroundTask $task The task to dispatch.
     * @throws \RuntimeException When no handler is registered for the task type.
     */
    public function dispatch(BackgroundTask $task): void;
}
