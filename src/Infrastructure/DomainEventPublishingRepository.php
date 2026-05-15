<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\DomainEventBusPublisher;
use SeedWork\Domain\AggregateRoot;
use SeedWork\Domain\Repository;

/**
 * Repository decorator that publishes domain events after saving an aggregate.
 *
 * Collects events via {@see AggregateRoot::getDomainEvents()} and forwards them
 * to the {@see DomainEventBusPublisher} after the inner repository persists the
 * aggregate. Events are only published on success; if save() throws, publish()
 * is not called.
 *
 * Depends on {@see DomainEventBusPublisher} (not the full {@see DomainEventBus})
 * to respect the Interface Segregation Principle — a repository only needs to
 * publish, not subscribe or dispatch.
 *
 * Example:
 * <code>
 * $repository = new DomainEventPublishingRepository(
 *     new DoctrineBankAccountRepository($entityManager),
 *     $deferredEventBus,
 * );
 * </code>
 *
 * @template T of AggregateRoot
 * @implements Repository<T>
 *
 * @see Repository              Domain port this decorates.
 * @see DomainEventBusPublisher Application port for publishing events.
 */
class DomainEventPublishingRepository implements Repository
{
    /**
     * @param Repository<T> $repository
     */
    public function __construct(
        private readonly Repository $repository,
        private readonly DomainEventBusPublisher $eventBus,
    ) {
    }

    /**
     * Persists the aggregate, then publishes its collected events.
     *
     * @param T $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot): void
    {
        $this->repository->save($aggregateRoot);
        $this->eventBus->publish($aggregateRoot->getDomainEvents());
    }

    /**
     * @return T|null
     */
    public function findById(mixed $id): ?AggregateRoot
    {
        return $this->repository->findById($id);
    }

    public function deleteById(mixed $id): void
    {
        $this->repository->deleteById($id);
    }
}
