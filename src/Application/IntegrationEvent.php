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
 * $id and $occurredAt are optional: omit them and the constructor auto-generates
 * a unique id ('evt-{uniqid}') and timestamps to UTC now.
 *
 * @see IntegrationEventPublisher Application port for publishing integration events.
 */
abstract readonly class IntegrationEvent
{
    public string $id;

    /**
     * @param string                     $type          Event name/topic (e.g. 'bc.aggregate.event_name').
     * @param string                     $version       Payload schema version (e.g. '1.0').
     * @param string                     $aggregateId   ID of the aggregate that raised the event
     * @param array<string, mixed>       $payload       serializable primitive facts
     * @param string                     $correlationId correlation ID for distributed tracing
     * @param string                     $id            unique event ID; auto-generated when empty
     * @param \DateTimeImmutable         $occurredAt    when the event occurred (UTC); defaults to now
     * @param null|string                $causationId   ID of the command or event that caused this one
     * @param null|array<string, string> $metadata      optional trace/tenant metadata
     */
    public function __construct(
        public string $type,
        public string $version,
        public string $aggregateId,
        public array $payload,
        public string $correlationId,
        string $id = '',
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable(
            'now',
            new \DateTimeZone('UTC')
        ),
        public ?string $causationId = null,
        public ?array $metadata = null
    ) {
        $this->id = '' !== $id ? $id : 'evt-'.uniqid('', true);
    }
}
