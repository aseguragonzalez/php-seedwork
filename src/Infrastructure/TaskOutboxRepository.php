<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\BackgroundTask;

/**
 * Repository for managing background task outbox records.
 *
 * Implementations save tasks, fetch pending ones for delivery,
 * and update their status after delivery attempts.
 *
 * @see TaskOutboxRecord    The outbox entry managed by this repository.
 * @see TaskOutboxStatus    Status transitions for outbox records.
 * @see BackgroundTask      The task stored in each outbox record.
 */
interface TaskOutboxRepository
{
    /**
     * Persists a new outbox record for the given task with Pending status.
     *
     * @param BackgroundTask $task the task to store
     */
    public function save(BackgroundTask $task): void;

    /**
     * Returns pending outbox records up to the given limit.
     *
     * @param int $limit maximum number of records to return
     *
     * @return list<TaskOutboxRecord> pending records ordered by creation time
     */
    public function findPending(int $limit = 100): array;

    /**
     * Marks the outbox record as successfully delivered.
     *
     * @param string $id outbox record ID
     */
    public function markAsDelivered(string $id): void;

    /**
     * Marks the outbox record as failed with the given error message.
     *
     * @param string $id    outbox record ID
     * @param string $error error description for diagnostics
     */
    public function markAsFailed(string $id, string $error): void;
}
