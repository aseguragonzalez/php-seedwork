<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\BackgroundTask;

/**
 * Immutable snapshot of a background task outbox entry.
 *
 * The outbox pattern ensures reliable scheduling of {@see BackgroundTask}s by
 * persisting them in a local store before delivering to a task runner.
 * Each record tracks delivery status and retry attempts.
 *
 * @see TaskOutboxRepository Repository that manages the lifecycle.
 * @see IntegrationEventOutboxStatus Reused status enum (Pending / Published→Delivered / Failed).
 */
final readonly class TaskOutboxRecord
{
    /**
     * @param string $id              Outbox record ID (distinct from the task ID).
     * @param BackgroundTask $task    The background task to be executed.
     * @param IntegrationEventOutboxStatus $status Current lifecycle status.
     * @param int $attempts           Number of delivery attempts so far.
     * @param \DateTimeImmutable $createdAt When this record was created (UTC).
     * @param string|null $lastError  Last error message if delivery failed.
     * @param \DateTimeImmutable|null $deliveredAt When the task was successfully delivered.
     */
    public function __construct(
        public string $id,
        public BackgroundTask $task,
        public IntegrationEventOutboxStatus $status,
        public int $attempts,
        public \DateTimeImmutable $createdAt,
        public ?string $lastError = null,
        public ?\DateTimeImmutable $deliveredAt = null
    ) {
    }
}
