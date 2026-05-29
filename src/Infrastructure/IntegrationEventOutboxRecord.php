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
     * @param string                       $id          the integration event's ID; used as the outbox record key
     * @param IntegrationEvent             $event       the integration event to be published
     * @param IntegrationEventOutboxStatus $status      current lifecycle status
     * @param int                          $attempts    number of publish attempts so far
     * @param \DateTimeImmutable           $createdAt   when this record was created (UTC)
     * @param null|string                  $lastError   last error message if the record failed
     * @param null|\DateTimeImmutable      $publishedAt when the event was successfully published
     */
    public function __construct(
        public string $id,
        public IntegrationEvent $event,
        public IntegrationEventOutboxStatus $status,
        public int $attempts,
        public \DateTimeImmutable $createdAt,
        public ?string $lastError = null,
        public ?\DateTimeImmutable $publishedAt = null
    ) {}
}
