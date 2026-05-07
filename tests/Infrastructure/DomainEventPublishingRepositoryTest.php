<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\DomainEventBus;
use SeedWork\Domain\AggregateRoot;
use SeedWork\Domain\DomainEvent;
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
        $eventA = $this->createMock(DomainEvent::class);
        $eventB = $this->createMock(DomainEvent::class);
        $events = [$eventA, $eventB];
        $aggregate = $this->createStub(AggregateRoot::class);
        $aggregate->method('collectEvents')->willReturn($events);

        $repository = $this->createStub(Repository::class);

        $eventBus = $this->createMock(DomainEventBus::class);
        $eventBus->expects($this->once())->method('publish')->with($events);

        $publishingRepo = new DomainEventPublishingRepository($repository, $eventBus);
        $publishingRepo->save($aggregate);
    }

    public function testSaveDoesNotPublishEventsWhenRepositoryThrows(): void
    {
        $aggregate = $this->createMock(AggregateRoot::class);
        $aggregate->expects($this->never())->method('collectEvents');

        $repository = $this->createStub(Repository::class);
        $repository->method('save')->willThrowException(new \RuntimeException('DB error'));

        $eventBus = $this->createMock(DomainEventBus::class);
        $eventBus->expects($this->never())->method('publish');

        $publishingRepo = new DomainEventPublishingRepository($repository, $eventBus);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB error');
        $publishingRepo->save($aggregate);
    }

    public function testFindByDelegatesToInnerRepository(): void
    {
        $id = $this->createStub(EntityId::class);
        $aggregate = $this->createStub(AggregateRoot::class);

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())->method('findBy')->with($id)->willReturn($aggregate);

        $eventBus = $this->createStub(DomainEventBus::class);

        $publishingRepo = new DomainEventPublishingRepository($repository, $eventBus);
        $result = $publishingRepo->findBy($id);

        self::assertSame($aggregate, $result);
    }

    public function testDeleteByDelegatesToInnerRepository(): void
    {
        $id = $this->createStub(EntityId::class);

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())->method('deleteBy')->with($id);

        $eventBus = $this->createStub(DomainEventBus::class);

        $publishingRepo = new DomainEventPublishingRepository($repository, $eventBus);
        $publishingRepo->deleteBy($id);
    }
}
