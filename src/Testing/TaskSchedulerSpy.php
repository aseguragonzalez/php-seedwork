<?php

declare(strict_types=1);

namespace SeedWork\Testing;

use SeedWork\Application\BackgroundTask;
use SeedWork\Application\TaskHandler;
use SeedWork\Application\TaskScheduler;

/**
 * Spy extension of {@see TaskScheduler} for use in tests.
 *
 * Adds introspection (scheduled()), handler registration (register()), synchronous
 * execution (executeScheduled()), and reset() so tests can drive and assert on
 * the full task scheduling flow in-process.
 *
 * @see InMemoryTaskScheduler Concrete spy implementation.
 */
interface TaskSchedulerSpy extends TaskScheduler
{
    /**
     * Returns all tasks scheduled so far.
     *
     * @return array<BackgroundTask>
     */
    public function scheduled(): array;

    /**
     * Registers a handler for the given task type.
     *
     * @param string $taskType Task type identifier (e.g. 'domain.action_name').
     * @param TaskHandler $handler Handler instance to invoke.
     */
    public function register(string $taskType, TaskHandler $handler): void;

    /**
     * Executes all scheduled tasks synchronously using registered handlers.
     * Tasks with no registered handler are silently skipped.
     */
    public function executeScheduled(): void;

    /**
     * Clears the scheduled task list. Handler registrations are preserved.
     */
    public function reset(): void;
}
