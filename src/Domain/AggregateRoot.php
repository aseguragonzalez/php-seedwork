<?php

declare(strict_types=1);

namespace Seedwork\Domain;

/**
 * @template TId of EntityId
 */
abstract readonly class AggregateRoot
{
    /**
     * @param TId $id
     * @param array<DomainEvent> $domainEvents
     */
    protected function __construct(public EntityId $id, private array $domainEvents = [])
    {
    }

    /**
     * @param AggregateRoot<TId> $other
     * @return bool
     */
    public function equals(AggregateRoot $other): bool
    {
        return $this->id->equals($other->id);
    }

    /**
     * @return array<DomainEvent>
     */
    public function collectEvents(): array
    {
        return array_map(
            fn (DomainEvent $domainEvent) => clone $domainEvent,
            $this->domainEvents
        );
    }
}
