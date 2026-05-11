<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\BackgroundTask;
use SeedWork\Application\TaskHandler;

/**
 * In-memory implementation of {@see TaskSchedulerSpy} for use in tests.
 *
 * Schedules tasks into an in-process list and executes them synchronously via
 * {@see executeScheduled()}, dispatching each task to its registered handler.
 * Tasks with no handler are silently skipped.
 *
 * @see TaskSchedulerSpy Test-focused extension implemented here.
 */
final class InMemoryTaskScheduler implements TaskSchedulerSpy
{
    /** @var list<BackgroundTask> */
    private array $scheduled = [];
    /** @var array<string, TaskHandler> */
    private array $handlers = [];

    /**
     * @return list<BackgroundTask>
     */
    public function scheduled(): array
    {
        return $this->scheduled;
    }

    public function register(string $taskType, TaskHandler $handler): void
    {
        $this->handlers[$taskType] = $handler;
    }

    public function schedule(BackgroundTask $task): void
    {
        $this->scheduled[] = $task;
    }

    public function executeScheduled(): void
    {
        foreach ($this->scheduled as $task) {
            $handler = $this->handlers[$task->type] ?? null;
            if ($handler !== null) {
                $handler->handle($task);
            }
        }
    }

    public function reset(): void
    {
        $this->scheduled = [];
        $this->handlers = [];
    }
}
