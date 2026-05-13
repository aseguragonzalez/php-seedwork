<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Infrastructure\InMemoryIntegrationEventOutboxRepository;
use SeedWork\Infrastructure\IntegrationEventOutboxStatus;
use Tests\Fixtures\FakeIntegrationEvent;

final class InMemoryIntegrationEventOutboxRepositoryTest extends TestCase
{
    private function createTestEvent(string $id = 'evt-001'): FakeIntegrationEvent
    {
        return new FakeIntegrationEvent($id);
    }

    public function testSaveCreatesAPendingRecord(): void
    {
        $repo = new InMemoryIntegrationEventOutboxRepository();
        $event = $this->createTestEvent();

        $repo->save($event);
        $pending = $repo->findPending();

        $this->assertCount(1, $pending);
        $this->assertSame(IntegrationEventOutboxStatus::Pending, $pending[0]->status);
        $this->assertSame($event->id, $pending[0]->id);
        $this->assertSame($event, $pending[0]->event);
    }

    public function testFindPendingReturnsOnlyPendingRecords(): void
    {
        $repo = new InMemoryIntegrationEventOutboxRepository();
        $repo->save($this->createTestEvent('evt-1'));
        $repo->save($this->createTestEvent('evt-2'));
        $repo->save($this->createTestEvent('evt-3'));
        $pending = $repo->findPending();
        $repo->markAsPublished($pending[0]->id);

        $remaining = $repo->findPending();

        $this->assertCount(2, $remaining);
    }

    public function testFindPendingRespectsLimit(): void
    {
        $repo = new InMemoryIntegrationEventOutboxRepository();
        $repo->save($this->createTestEvent('evt-1'));
        $repo->save($this->createTestEvent('evt-2'));
        $repo->save($this->createTestEvent('evt-3'));

        $pending = $repo->findPending(2);

        $this->assertCount(2, $pending);
    }

    public function testMarkAsPublishedChangesStatus(): void
    {
        $repo = new InMemoryIntegrationEventOutboxRepository();
        $repo->save($this->createTestEvent());
        $pending = $repo->findPending();
        $recordId = $pending[0]->id;

        $repo->markAsPublished($recordId);

        $this->assertCount(0, $repo->findPending());
    }

    public function testMarkAsPublishedSetsPublishedAt(): void
    {
        $repo = new InMemoryIntegrationEventOutboxRepository();
        $repo->save($this->createTestEvent());
        $pending = $repo->findPending();
        $recordId = $pending[0]->id;

        $repo->markAsPublished($recordId);

        $all = $repo->all();
        $this->assertCount(1, $all);
        $this->assertSame(IntegrationEventOutboxStatus::Published, $all[0]->status);
        $this->assertNotNull($all[0]->publishedAt);
    }

    public function testMarkAsFailedChangesStatusAndRecordsError(): void
    {
        $repo = new InMemoryIntegrationEventOutboxRepository();
        $repo->save($this->createTestEvent());
        $pending = $repo->findPending();
        $recordId = $pending[0]->id;

        $repo->markAsFailed($recordId, 'Connection timeout');

        $this->assertCount(0, $repo->findPending());
        $all = $repo->all();
        $this->assertSame(IntegrationEventOutboxStatus::Failed, $all[0]->status);
        $this->assertSame('Connection timeout', $all[0]->lastError);
    }

    public function testMarkAsFailedIncrementsAttempts(): void
    {
        $repo = new InMemoryIntegrationEventOutboxRepository();
        $repo->save($this->createTestEvent());
        $pending = $repo->findPending();
        $recordId = $pending[0]->id;

        $this->assertSame(0, $pending[0]->attempts);

        $repo->markAsFailed($recordId, 'err');
        $all = $repo->all();

        $this->assertSame(1, $all[0]->attempts);
    }

    public function testAllReturnsAllRecordsRegardlessOfStatus(): void
    {
        $repo = new InMemoryIntegrationEventOutboxRepository();
        $repo->save($this->createTestEvent('evt-1'));
        $repo->save($this->createTestEvent('evt-2'));
        $pending = $repo->findPending();
        $repo->markAsPublished($pending[0]->id);

        $all = $repo->all();

        $this->assertCount(2, $all);
    }

    public function testResetClearsAllRecords(): void
    {
        $repo = new InMemoryIntegrationEventOutboxRepository();
        $repo->save($this->createTestEvent('evt-1'));
        $repo->save($this->createTestEvent('evt-2'));

        $repo->reset();

        $this->assertCount(0, $repo->all());
        $this->assertCount(0, $repo->findPending());
    }

    public function testMarkAsPublishedIsNoopForUnknownId(): void
    {
        $repo = new InMemoryIntegrationEventOutboxRepository();
        $repo->save($this->createTestEvent('evt-1'));

        $repo->markAsPublished('non-existent-id');

        $pending = $repo->findPending();
        $this->assertCount(1, $pending);
        $this->assertSame(IntegrationEventOutboxStatus::Pending, $pending[0]->status);
    }

    public function testMarkAsFailedIsNoopForUnknownId(): void
    {
        $repo = new InMemoryIntegrationEventOutboxRepository();
        $repo->save($this->createTestEvent('evt-1'));

        $repo->markAsFailed('non-existent-id', 'some error');

        $pending = $repo->findPending();
        $this->assertCount(1, $pending);
        $this->assertSame(IntegrationEventOutboxStatus::Pending, $pending[0]->status);
    }
}
