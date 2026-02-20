<?php

declare(strict_types=1);

namespace Seedwork\Domain;

/**
 * Base type for Domain-Driven Design Domain Events.
 *
 * A Domain Event is a record of something that happened in the domain, expressed
 * in past tense (e.g. MoneyDeposited, OrderPlaced). It is immutable and carries
 * a payload that describes the change. Events are raised by aggregates and
 * collected for publication so other parts of the system (projections, other
 * bounded contexts) can react without coupling to the aggregate's internals.
 *
 * The type and version support schema evolution: consumers can dispatch or
 * deserialize by type and handle multiple payload versions. Use equals() for
 * deduplication (same EventId = same event).
 *
 * @see EventId Unique identifier for this event instance (e.g. for idempotency).
 * @see AggregateRoot Aggregates record events and expose them via collectEvents().
 * @see https://domainlanguage.com/ddd/ Eric Evans, "Domain-Driven Design" – Domain Events.
 * @see https://martinfowler.com/eaaDev/DomainEvent.html Martin Fowler, P of EAA – Domain Event.
 * @see https://udidahan.com/2008/08/25/domain-events-take-2/ Udi Dahan, "Domain Events – Take 2".
 */
abstract readonly class DomainEvent
{
    /**
     * Constructs an immutable domain event.
     *
     * Subclasses typically accept domain types (e.g. Money, EntityId), then map
     * them to a serializable payload (scalars, arrays) for storage or messaging.
     *
     * @param EventId $id Unique identity of this event (e.g. UUID); used for equality and deduplication.
     * @param string $type Event name or topic (e.g. 'bank_account.money_deposited'); stable across versions.
     * @param string $version Payload schema version (e.g. '1.0') to support evolution and deserialization.
     * @param array<string, mixed> $payload Serializable facts like arrays; avoid passing non-serializable objects.
     * @param \DateTimeImmutable $createdAt When the event occurred; use UTC for consistency.
     */
    protected function __construct(
        public EventId $id,
        public string $type,
        public string $version,
        public array $payload = [],
        public \DateTimeImmutable $createdAt = new \DateTimeImmutable(
            'now',
            new \DateTimeZone('UTC')
        )
    ) {
    }

    /**
     * Identity-based equality: two events are equal iff they have the same ID.
     *
     * Use when deduplicating (e.g. same event received twice from a message bus).
     *
     * @param DomainEvent $other Another domain event.
     * @return bool True if both have the same EventId.
     */
    public function equals(DomainEvent $other): bool
    {
        return $this->id->equals($other->id);
    }
}
