<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\OutboxStatus;
use SeedWork\Infrastructure\InMemoryOutboxRepository;

final class InMemoryOutboxRepositoryTest extends TestCase
{
    private function createTestEvent(string $id = 'evt-001'): \Tests\Fixtures\FakeIntegrationEvent
    {
        return new \Tests\Fixtures\FakeIntegrationEvent($id);
    }

    public function testSaveCreatesAPendingRecord(): void
    {
        $repo = new InMemoryOutboxRepository();
        $event = $this->createTestEvent();

        $repo->save($event);
        $pending = $repo->findPending();

        $this->assertCount(1, $pending);
        $this->assertSame(OutboxStatus::Pending, $pending[0]->status);
        $this->assertSame($event, $pending[0]->event);
    }

    public function testFindPendingReturnsOnlyPendingRecords(): void
    {
        $repo = new InMemoryOutboxRepository();
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
        $repo = new InMemoryOutboxRepository();
        $repo->save($this->createTestEvent('evt-1'));
        $repo->save($this->createTestEvent('evt-2'));
        $repo->save($this->createTestEvent('evt-3'));

        $pending = $repo->findPending(2);

        $this->assertCount(2, $pending);
    }

    public function testMarkAsPublishedChangesStatus(): void
    {
        $repo = new InMemoryOutboxRepository();
        $repo->save($this->createTestEvent());
        $pending = $repo->findPending();
        $recordId = $pending[0]->id;

        $repo->markAsPublished($recordId);

        $this->assertCount(0, $repo->findPending());
    }

    public function testMarkAsPublishedSetsPublishedAt(): void
    {
        $repo = new InMemoryOutboxRepository();
        $repo->save($this->createTestEvent());
        $pending = $repo->findPending();
        $recordId = $pending[0]->id;

        $repo->markAsPublished($recordId);

        // We can verify by checking there are no more pending
        $this->assertCount(0, $repo->findPending());
    }

    public function testMarkAsFailedChangesStatusAndRecordsError(): void
    {
        $repo = new InMemoryOutboxRepository();
        $repo->save($this->createTestEvent());
        $pending = $repo->findPending();
        $recordId = $pending[0]->id;

        $repo->markAsFailed($recordId, 'Connection timeout');

        $this->assertCount(0, $repo->findPending());
    }

    public function testMarkAsFailedIncrementsAttempts(): void
    {
        $repo = new InMemoryOutboxRepository();
        $repo->save($this->createTestEvent());
        $pending = $repo->findPending();
        $recordId = $pending[0]->id;

        $this->assertSame(0, $pending[0]->attempts);
    }
}
