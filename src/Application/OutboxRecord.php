<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Immutable snapshot of an outbox entry.
 *
 * The outbox pattern ensures reliable delivery of {@see IntegrationEvent}s by
 * persisting them in a local store before publishing to an external broker.
 * Each record tracks delivery status and retry attempts.
 *
 * @see OutboxRepository Repository that manages OutboxRecord lifecycle.
 * @see OutboxStatus      Lifecycle status values.
 */
final readonly class OutboxRecord
{
    /**
     * @param string $id              Outbox record ID (distinct from the event ID).
     * @param IntegrationEvent $event The integration event to be published.
     * @param OutboxStatus $status    Current lifecycle status.
     * @param int $attempts           Number of publish attempts so far.
     * @param \DateTimeImmutable $createdAt When this record was created (UTC).
     * @param string|null $lastError  Last error message if the record failed.
     * @param \DateTimeImmutable|null $publishedAt When the event was successfully published.
     */
    public function __construct(
        public string $id,
        public IntegrationEvent $event,
        public OutboxStatus $status,
        public int $attempts,
        public \DateTimeImmutable $createdAt,
        public ?string $lastError = null,
        public ?\DateTimeImmutable $publishedAt = null
    ) {
    }
}
