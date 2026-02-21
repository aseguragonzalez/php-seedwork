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
 * layer calls {@see collectEvents()} (e.g. after handling a command) to retrieve
 * events for publishing, without mutating the aggregate's internal list.
 *
 * @see DomainEvent Events recorded by the aggregate for downstream consumers.
 * @see EntityId Subclasses use a dedicated ID type (e.g. BankAccountId) as TId.
 * @see https://domainlanguage.com/ddd/ Eric Evans, "Domain-Driven Design" – Aggregates (Ch. 6).
 * @see https://martinfowler.com/bliki/DDD_Aggregate.html Martin Fowler, P of EAA – DDD Aggregate.
 *
 * @template TId of EntityId
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
    protected function __construct(public EntityId $id, private array $domainEvents = [])
    {
        $this->validate();
    }

    /**
     * Identity-based equality: two aggregate roots are equal iff they have the same ID.
     *
     * @param AggregateRoot<TId> $other Another aggregate root (typically same concrete type).
     * @return bool True if both have the same identity.
     */
    public function equals(AggregateRoot $other): bool
    {
        return $this->id->equals($other->id);
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
    public function collectEvents(): array
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
