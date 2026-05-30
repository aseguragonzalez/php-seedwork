<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application port for scheduling background tasks for async execution.
 *
 * Implementations may persist tasks via an outbox for reliable delivery,
 * enqueue them directly, or buffer them in-memory for testing.
 *
 * @see BackgroundTask Tasks scheduled through this port.
 * @see TaskHandler    Handler that executes a specific task type.
 */
interface TaskScheduler
{
    /**
     * Schedules a background task for async execution.
     *
     * @param BackgroundTask $task the task to schedule
     */
    public function schedule(BackgroundTask $task): void;
}
