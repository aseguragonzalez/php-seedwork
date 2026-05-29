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
 * @see TaskOutboxStatus     Status transitions for outbox records.
 */
final readonly class TaskOutboxRecord
{
    /**
     * @param string                  $id          the background task's ID; used as the outbox record key
     * @param BackgroundTask          $task        the background task to be executed
     * @param TaskOutboxStatus        $status      current lifecycle status
     * @param int                     $attempts    number of delivery attempts so far
     * @param \DateTimeImmutable      $createdAt   when this record was created (UTC)
     * @param null|string             $lastError   last error message if delivery failed
     * @param null|\DateTimeImmutable $deliveredAt when the task was successfully delivered
     */
    public function __construct(
        public string $id,
        public BackgroundTask $task,
        public TaskOutboxStatus $status,
        public int $attempts,
        public \DateTimeImmutable $createdAt,
        public ?string $lastError = null,
        public ?\DateTimeImmutable $deliveredAt = null
    ) {}
}
