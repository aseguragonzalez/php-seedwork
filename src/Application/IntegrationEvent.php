<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Base type for Integration Events — messages sent across bounded context boundaries.
 *
 * An Integration Event carries enough context for consumers in other bounded contexts
 * to react without needing to call back into the source. It is a serializable snapshot
 * with explicit schema versioning (type + version) to support evolution.
 *
 * @see IntegrationEventPublisher Application port for publishing integration events.
 * @see OutboxRepository Outbox pattern for reliable delivery.
 */
abstract readonly class IntegrationEvent
{
    /**
     * @param string $id              Unique event ID (UUID).
     * @param string $type            Event name/topic (e.g. 'bc.aggregate.event_name').
     * @param string $version         Payload schema version (e.g. '1.0').
     * @param string $aggregateId     ID of the aggregate that raised the event.
     * @param \DateTimeImmutable $occurredAt When the event occurred (UTC).
     * @param array<string, mixed> $payload  Serializable primitive facts.
     * @param string $correlationId   Correlation ID for distributed tracing (required).
     * @param string|null $causationId ID of the command or event that caused this one.
     * @param array<string, mixed>|null $metadata Optional trace/tenant metadata.
     */
    public function __construct(
        public string $id,
        public string $type,
        public string $version,
        public string $aggregateId,
        public \DateTimeImmutable $occurredAt,
        public array $payload,
        public string $correlationId,
        public ?string $causationId = null,
        public ?array $metadata = null
    ) {
    }
}
