<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Base type for background tasks — units of work scheduled for async execution.
 *
 * A BackgroundTask carries its payload as serializable primitives, tracks
 * execution state (attempts, status, timestamps), and supports retry via
 * {@see TaskQueue::nack()}. Subclasses add domain-specific fields.
 *
 * @see TaskQueue   Queue that manages the lifecycle of background tasks.
 * @see TaskHandler Handler that executes a specific task type.
 * @see TaskBus     Dispatches tasks to the appropriate handler.
 */
abstract readonly class BackgroundTask
{
    /**
     * @param string $id              Unique task ID (UUID).
     * @param string $type            Task type identifier (e.g. 'domain.action_name').
     * @param array<string, mixed> $payload Serializable primitive arguments.
     * @param TaskStatus $status      Current lifecycle status.
     * @param \DateTimeImmutable $scheduledAt When the task should be executed (UTC).
     * @param int $attempts           Number of execution attempts so far.
     * @param int $maxAttempts        Maximum allowed attempts before marking as failed.
     * @param string $correlationId   Correlation ID for distributed tracing (required).
     * @param \DateTimeImmutable|null $startedAt   When the task started running.
     * @param \DateTimeImmutable|null $completedAt When the task completed successfully.
     * @param string|null $lastError  Last error message if execution failed.
     * @param string|null $causationId ID of the command or event that caused this task.
     */
    public function __construct(
        public string $id,
        public string $type,
        public array $payload,
        public TaskStatus $status,
        public \DateTimeImmutable $scheduledAt,
        public int $attempts,
        public int $maxAttempts,
        public string $correlationId,
        public ?\DateTimeImmutable $startedAt = null,
        public ?\DateTimeImmutable $completedAt = null,
        public ?string $lastError = null,
        public ?string $causationId = null
    ) {
    }
}
