<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\IntegrationEvent;

/**
 * Repository for managing integration event outbox records.
 *
 * Implementations save integration events, fetch pending ones for publishing,
 * and update their status after delivery attempts.
 *
 * @see IntegrationEventOutboxRecord  The outbox entry managed by this repository.
 * @see IntegrationEventOutboxStatus  Status transitions for outbox records.
 * @see IntegrationEvent The event stored in each outbox record.
 */
interface IntegrationEventOutboxRepository
{
    /**
     * Persists a new outbox record for the given integration event with Pending status.
     *
     * @param IntegrationEvent $event The event to store.
     */
    public function save(IntegrationEvent $event): void;

    /**
     * Returns pending outbox records up to the given limit.
     *
     * @param int $limit Maximum number of records to return.
     * @return list<IntegrationEventOutboxRecord> Pending records ordered by creation time.
     */
    public function findPending(int $limit = 100): array;

    /**
     * Marks the outbox record as successfully published.
     *
     * @param string $id Outbox record ID.
     */
    public function markAsPublished(string $id): void;

    /**
     * Marks the outbox record as failed with the given error message.
     *
     * @param string $id    Outbox record ID.
     * @param string $error Error description for diagnostics.
     */
    public function markAsFailed(string $id, string $error): void;
}
