<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\BackgroundTask;
use SeedWork\Application\TaskQueue;
use SeedWork\Application\TaskStatus;

/**
 * In-memory implementation of {@see TaskQueue} for use in tests and examples.
 *
 * @see TaskQueue Application port this implements.
 */
final class InMemoryTaskQueue implements TaskQueue
{
    /** @var array<string, BackgroundTask> */
    private array $store = [];
    /** @var list<string> */
    private array $queue = [];

    public function enqueue(BackgroundTask $task): void
    {
        $this->store[$task->id] = $task;
        $this->queue[] = $task->id;
    }

    public function dequeue(): ?BackgroundTask
    {
        foreach ($this->queue as $index => $id) {
            $task = $this->store[$id] ?? null;
            if ($task === null || $task->status !== TaskStatus::Pending) {
                continue;
            }
            $this->removeFromQueue($index);
            $running = $this->copy($task, status: TaskStatus::Running, startedAt: new \DateTimeImmutable());
            $this->store[$id] = $running;
            return $running;
        }
        return null;
    }

    public function ack(string $taskId): void
    {
        $task = $this->store[$taskId] ?? null;
        if ($task === null) {
            return;
        }
        $this->store[$taskId] = $this->copy($task, status: TaskStatus::Completed, completedAt: new \DateTimeImmutable());
    }

    public function nack(string $taskId, string $error): void
    {
        $task = $this->store[$taskId] ?? null;
        if ($task === null) {
            return;
        }
        $attempts = $task->attempts + 1;
        if ($attempts >= $task->maxAttempts) {
            $this->store[$taskId] = $this->copy($task, status: TaskStatus::Failed, attempts: $attempts, lastError: $error);
        } else {
            $updated = $this->copy($task, status: TaskStatus::Pending, attempts: $attempts, lastError: $error);
            $this->store[$taskId] = $updated;
            $this->queue[] = $taskId;
        }
    }

    public function findById(string $taskId): ?BackgroundTask
    {
        return $this->store[$taskId] ?? null;
    }

    private function removeFromQueue(int $index): void
    {
        /** @var list<string> $newQueue */
        $newQueue = [];
        foreach ($this->queue as $i => $id) {
            if ($i !== $index) {
                $newQueue[] = $id;
            }
        }
        $this->queue = $newQueue;
    }

    private function copy(
        BackgroundTask $task,
        ?TaskStatus $status = null,
        ?int $attempts = null,
        ?\DateTimeImmutable $startedAt = null,
        ?\DateTimeImmutable $completedAt = null,
        ?string $lastError = null
    ): BackgroundTask {
        return new readonly class (
            $task->id,
            $task->type,
            $task->payload,
            $status ?? $task->status,
            $task->scheduledAt,
            $attempts ?? $task->attempts,
            $task->maxAttempts,
            $task->correlationId,
            $startedAt ?? $task->startedAt,
            $completedAt ?? $task->completedAt,
            $lastError ?? $task->lastError,
            $task->causationId
        ) extends BackgroundTask {};
    }
}
