<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Queue port for background task lifecycle management.
 *
 * Implementations provide atomic claim (dequeue), acknowledgement (ack),
 * and negative acknowledgement (nack) semantics for reliable task execution.
 *
 * @see BackgroundTask Tasks managed by this queue.
 * @see TaskStatus     Status transitions driven by this queue.
 */
interface TaskQueue
{
    /**
     * Enqueues a background task for later execution.
     *
     * @param BackgroundTask $task The task to enqueue.
     */
    public function enqueue(BackgroundTask $task): void;

    /**
     * Atomically claims and returns the next pending task, or null if none.
     * The returned task is marked as Running.
     *
     * @return BackgroundTask|null The next pending task, or null if the queue is empty.
     */
    public function dequeue(): ?BackgroundTask;

    /**
     * Acknowledges successful execution of a task. Marks it as Completed.
     *
     * @param string $taskId The task ID to acknowledge.
     */
    public function ack(string $taskId): void;

    /**
     * Negatively acknowledges a task execution failure. Increments attempts;
     * re-enqueues the task as Pending if attempts < maxAttempts, or marks it
     * as Failed if the limit is reached.
     *
     * @param string $taskId The task ID to nack.
     * @param string $error  Error description for diagnostics.
     */
    public function nack(string $taskId, string $error): void;

    /**
     * Returns the task with the given ID, or null if not found.
     *
     * @param string $taskId The task ID to look up.
     * @return BackgroundTask|null The task, or null if not found.
     */
    public function findById(string $taskId): ?BackgroundTask;
}
