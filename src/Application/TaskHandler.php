<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Contract for executing a single type of background task.
 *
 * Handlers are registered on a {@see TaskBus} via register($taskType, $handler).
 * When a task of that type is dispatched, the bus invokes handle() with the task.
 * Implement handle() idempotently when tasks may be retried.
 *
 * @see BackgroundTask Tasks passed to handle().
 * @see TaskBus        Dispatches tasks to the appropriate handler.
 */
interface TaskHandler
{
    /**
     * Executes the background task.
     *
     * @param BackgroundTask $task The task to handle.
     */
    public function handle(BackgroundTask $task): void;
}
