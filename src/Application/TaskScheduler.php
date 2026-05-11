<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Application port for scheduling background tasks for async execution.
 *
 * Implementations may persist the task to an outbox ({@see OutboxTaskScheduler}),
 * enqueue it directly, or buffer it for testing ({@see InMemoryTaskScheduler}).
 *
 * @see BackgroundTask Tasks scheduled through this port.
 * @see TaskHandler    Handler that executes a specific task type.
 */
interface TaskScheduler
{
    /**
     * Schedules a background task for async execution.
     *
     * @param BackgroundTask $task The task to schedule.
     */
    public function schedule(BackgroundTask $task): void;
}
