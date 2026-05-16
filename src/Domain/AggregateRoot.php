<?php

declare(strict_types=1);

namespace SeedWork\Domain;

/**
 * Base type for Domain-Driven Design Aggregate Roots.
 *
 * An Aggregate is a cluster of entities and value objects with a consistency boundary:
 * invariants are enforced inside the boundary, and the Aggregate Root is the only
 * entry point for changes. External references hold only the root's identity;
 * all modifications go through the root so the aggregate can remain consistent.
 *
 * This base adds support for recording domain events. When the root applies a
 * state change, it can append a DomainEvent. The application or infrastructure
 * layer calls {@see getDomainEvents()} (e.g. after handling a command) to retrieve
 * events for publishing, without mutating the aggregate's internal list.
 *
 * The identity type TId is unconstrained: use any type your bounded context
 * prefers — a plain string, an int, a UUID, or a custom value object.
 *
 * @see DomainEvent Events recorded by the aggregate for downstream consumers.
 * @see https://domainlanguage.com/ddd/ Eric Evans, "Domain-Driven Design" – Aggregates (Ch. 6).
 * @see https://martinfowler.com/bliki/DDD_Aggregate.html Martin Fowler, P of EAA – DDD Aggregate.
 *
 * @template TId
 */
abstract readonly class AggregateRoot
{
    /**
     * Constructs the aggregate with its identity and optionally pre-recorded domain events.
     *
     * Subclasses typically pass through events when creating a new instance after a
     * state change (e.g. withdraw adds MoneyWithdrawn and forwards existing events).
     *
     * @param TId $id Unique identity of this aggregate; also the consistency boundary identifier.
     * @param array<DomainEvent> $domainEvents Events already recorded (e.g. from previous operations in the same flow).
     */
    protected function __construct(public mixed $id, private array $domainEvents = [])
    {
        $this->validate();
    }

    /**
     * Identity-based equality: two aggregate roots are equal iff they are of the same
     * concrete type and their IDs produce the same string representation.
     *
     * The class guard prevents cross-type false positives (e.g. Order#1 == Product#1).
     * String-cast strict comparison avoids PHP loose-equality quirks ("0e123" == 0).
     * TId must be stringable (string, int, or object with __toString()).
     *
     * @param AggregateRoot<TId> $other Another aggregate root (typically same concrete type).
     * @return bool True if both have the same concrete type and identity.
     */
    public function equals(AggregateRoot $other): bool
    {
        /** @var string|int|\Stringable $thisId */
        $thisId = $this->id;
        /** @var string|int|\Stringable $otherId */
        $otherId = $other->id;

        return $this::class === $other::class
            && (string) $thisId === (string) $otherId;
    }

    /**
     * Returns copies of all domain events recorded by this aggregate.
     *
     * Cloning ensures consumers cannot mutate the aggregate's internal event list.
     * Call this after executing a command (and optionally after persisting the root)
     * to dispatch events to other bounded contexts or read models.
     *
     * @return array<DomainEvent> Cloned events for publishing; does not clear the aggregate.
     */
    public function getDomainEvents(): array
    {
        return array_map(
            fn (DomainEvent $domainEvent) => clone $domainEvent,
            $this->domainEvents
        );
    }

    /**
     * Validates aggregate invariants after construction.
     *
     * Override in subclasses to enforce rules that must always hold within this
     * aggregate (e.g. balance ≥ 0, required associations). Called from the constructor.
     */
    abstract protected function validate(): void;
}
