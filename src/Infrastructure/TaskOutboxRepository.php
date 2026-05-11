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
 * @see TaskOutboxRecord         The outbox entry managed by this repository.
 * @see IntegrationEventOutboxStatus Status transitions for outbox records.
 * @see BackgroundTask           The task stored in each outbox record.
 */
interface TaskOutboxRepository
{
    /**
     * Persists a new outbox record for the given task with Pending status.
     *
     * @param BackgroundTask $task The task to store.
     */
    public function save(BackgroundTask $task): void;

    /**
     * Returns pending outbox records up to the given limit.
     *
     * @param int $limit Maximum number of records to return.
     * @return array<TaskOutboxRecord> Pending records ordered by creation time.
     */
    public function findPending(int $limit = 100): array;

    /**
     * Marks the outbox record as successfully delivered.
     *
     * @param string $id Outbox record ID.
     */
    public function markAsDelivered(string $id): void;

    /**
     * Marks the outbox record as failed with the given error message.
     *
     * @param string $id    Outbox record ID.
     * @param string $error Error description for diagnostics.
     */
    public function markAsFailed(string $id, string $error): void;
}
