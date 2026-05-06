<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\DomainEventBus;
use SeedWork\Domain\AggregateRoot;
use SeedWork\Domain\EntityId;
use SeedWork\Domain\Repository;
use SeedWork\Infrastructure\DomainEventPublishingRepository;

final class DomainEventPublishingRepositoryTest extends TestCase
{
    public function testSaveDelegatesToRepositoryThenPublishesCollectedEvents(): void
    {
        $aggregate = $this->createMock(AggregateRoot::class);
        $aggregate->expects($this->once())->method('collectEvents')->willReturn([]);

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())->method('save')->with($aggregate);

        $eventBus = $this->createMock(DomainEventBus::class);
        $eventBus->expects($this->once())->method('publish')->with([]);

        $publishingRepo = new DomainEventPublishingRepository($repository, $eventBus);
        $publishingRepo->save($aggregate);
    }

    public function testSavePublishesEventsReturnedByAggregate(): void
    {
        $events = ['event-a', 'event-b'];
        $aggregate = $this->createMock(AggregateRoot::class);
        $aggregate->method('collectEvents')->willReturn($events);

        $repository = $this->createMock(Repository::class);

        $eventBus = $this->createMock(DomainEventBus::class);
        $eventBus->expects($this->once())->method('publish')->with($events);

        $publishingRepo = new DomainEventPublishingRepository($repository, $eventBus);
        $publishingRepo->save($aggregate);
    }

    public function testFindByDelegatesToInnerRepository(): void
    {
        $id = $this->createMock(EntityId::class);
        $aggregate = $this->createMock(AggregateRoot::class);

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())->method('findBy')->with($id)->willReturn($aggregate);

        $eventBus = $this->createMock(DomainEventBus::class);

        $publishingRepo = new DomainEventPublishingRepository($repository, $eventBus);
        $result = $publishingRepo->findBy($id);

        self::assertSame($aggregate, $result);
    }

    public function testDeleteByDelegatesToInnerRepository(): void
    {
        $id = $this->createMock(EntityId::class);

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())->method('deleteBy')->with($id);

        $eventBus = $this->createMock(DomainEventBus::class);

        $publishingRepo = new DomainEventPublishingRepository($repository, $eventBus);
        $publishingRepo->deleteBy($id);
    }
}
