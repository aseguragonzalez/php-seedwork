<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\DomainEventBus;
use SeedWork\Domain\AggregateRoot;
use SeedWork\Domain\EntityId;
use SeedWork\Domain\Repository;

/**
 * Repository decorator that publishes domain events after saving an aggregate.
 *
 * Collects events via {@see AggregateRoot::collectEvents()} and forwards them
 * to the {@see DomainEventBus} after the inner repository persists the aggregate.
 * Events are only published on success; if save() throws, publish() is not called.
 *
 * Use this decorator so command handlers do not need to publish events manually.
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
 * @see Repository     Domain port this decorates.
 * @see DomainEventBus Application port for publishing events.
 */
final class DomainEventPublishingRepository implements Repository
{
    /**
     * @param Repository<T> $repository
     */
    public function __construct(
        private readonly Repository $repository,
        private readonly DomainEventBus $domainEventBus,
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
        $this->domainEventBus->publish($aggregateRoot->collectEvents());
    }

    /**
     * @return T|null
     */
    public function findBy(EntityId $id): ?AggregateRoot
    {
        return $this->repository->findBy($id);
    }

    public function deleteBy(EntityId $id): void
    {
        $this->repository->deleteBy($id);
    }
}
