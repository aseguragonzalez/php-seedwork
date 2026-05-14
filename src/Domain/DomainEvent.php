<?php

declare(strict_types=1);

namespace SeedWork\Domain;

/**
 * Base type for Domain-Driven Design Domain Events.
 *
 * A Domain Event is a record of something that happened in the domain, expressed
 * in past tense (e.g. MoneyDeposited, OrderPlaced). It is immutable; subclasses
 * expose event-specific facts as their own readonly properties. Events are raised
 * by aggregates and collected for publication so other parts of the system
 * (projections, other bounded contexts) can react without coupling to the
 * aggregate's internals.
 *
 * Use equals() for deduplication (same EventId = same event).
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
     * @param EventId $id Unique identity of this event (e.g. UUID); used for equality and deduplication.
     * @param \DateTimeImmutable $createdAt When the event occurred; use UTC for consistency.
     */
    protected function __construct(
        public EventId $id,
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
