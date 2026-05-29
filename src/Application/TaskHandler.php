<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Contract for executing a single type of background task.
 *
 * Implement handle() idempotently when tasks may be retried.
 *
 * @see BackgroundTask Tasks passed to handle().
 * @see TaskScheduler  Port for scheduling tasks.
 */
interface TaskHandler
{
    /**
     * Executes the background task.
     *
     * @param BackgroundTask $task the task to handle
     */
    public function handle(BackgroundTask $task): void;
}
