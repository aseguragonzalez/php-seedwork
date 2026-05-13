<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\IntegrationEvent;

/**
 * Immutable snapshot of an integration event outbox entry.
 *
 * The outbox pattern ensures reliable delivery of {@see IntegrationEvent}s by
 * persisting them in a local store before publishing to an external broker.
 * Each record tracks delivery status and retry attempts.
 *
 * @see IntegrationEventOutboxRepository Repository that manages the lifecycle.
 * @see IntegrationEventOutboxStatus      Lifecycle status values.
 */
final readonly class IntegrationEventOutboxRecord
{
    /**
     * @param string $id              The integration event's ID; used as the outbox record key.
     * @param IntegrationEvent $event The integration event to be published.
     * @param IntegrationEventOutboxStatus $status Current lifecycle status.
     * @param int $attempts           Number of publish attempts so far.
     * @param \DateTimeImmutable $createdAt When this record was created (UTC).
     * @param string|null $lastError  Last error message if the record failed.
     * @param \DateTimeImmutable|null $publishedAt When the event was successfully published.
     */
    public function __construct(
        public string $id,
        public IntegrationEvent $event,
        public IntegrationEventOutboxStatus $status,
        public int $attempts,
        public \DateTimeImmutable $createdAt,
        public ?string $lastError = null,
        public ?\DateTimeImmutable $publishedAt = null
    ) {
    }
}
