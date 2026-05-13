<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\BackgroundTask;
use SeedWork\Infrastructure\InMemoryTaskOutboxRepository;
use SeedWork\Infrastructure\TaskOutboxStatus;

final class InMemoryTaskOutboxRepositoryTest extends TestCase
{
    private function createTask(string $id = 'task-001', string $type = 'domain.test_task'): BackgroundTask
    {
        return new readonly class ($id, $type) extends BackgroundTask {
            public function __construct(string $id, string $type)
            {
                parent::__construct(
                    id: $id,
                    type: $type,
                    payload: ['key' => 'value'],
                    correlationId: 'corr-001'
                );
            }
        };
    }

    public function testSaveCreatesAPendingRecord(): void
    {
        $repo = new InMemoryTaskOutboxRepository();
        $task = $this->createTask();

        $repo->save($task);
        $pending = $repo->findPending();

        $this->assertCount(1, $pending);
        $this->assertSame(TaskOutboxStatus::Pending, $pending[0]->status);
        $this->assertSame($task->id, $pending[0]->id);
        $this->assertSame($task, $pending[0]->task);
    }

    public function testSaveIsIdempotentForSameTaskId(): void
    {
        $repo = new InMemoryTaskOutboxRepository();
        $task = $this->createTask('task-001');

        $repo->save($task);
        $repo->save($task);

        $this->assertCount(1, $repo->all());
    }

    public function testSaveDoesNotOverwriteDeliveredRecord(): void
    {
        $repo = new InMemoryTaskOutboxRepository();
        $task = $this->createTask('task-001');
        $repo->save($task);
        $repo->markAsDelivered('task-001');

        $repo->save($task);

        $all = $repo->all();
        $this->assertCount(1, $all);
        $this->assertSame(TaskOutboxStatus::Delivered, $all[0]->status);
    }

    public function testFindPendingReturnsOnlyPendingRecords(): void
    {
        $repo = new InMemoryTaskOutboxRepository();
        $repo->save($this->createTask('task-1'));
        $repo->save($this->createTask('task-2'));
        $repo->save($this->createTask('task-3'));
        $pending = $repo->findPending();
        $repo->markAsDelivered($pending[0]->id);

        $remaining = $repo->findPending();

        $this->assertCount(2, $remaining);
    }

    public function testFindPendingRespectsLimit(): void
    {
        $repo = new InMemoryTaskOutboxRepository();
        $repo->save($this->createTask('task-1'));
        $repo->save($this->createTask('task-2'));
        $repo->save($this->createTask('task-3'));

        $pending = $repo->findPending(2);

        $this->assertCount(2, $pending);
    }

    public function testMarkAsDeliveredChangesStatus(): void
    {
        $repo = new InMemoryTaskOutboxRepository();
        $repo->save($this->createTask());
        $pending = $repo->findPending();
        $recordId = $pending[0]->id;

        $repo->markAsDelivered($recordId);

        $this->assertCount(0, $repo->findPending());
        $all = $repo->all();
        $this->assertSame(TaskOutboxStatus::Delivered, $all[0]->status);
        $this->assertNotNull($all[0]->deliveredAt);
    }

    public function testMarkAsFailedChangesStatusAndRecordsError(): void
    {
        $repo = new InMemoryTaskOutboxRepository();
        $repo->save($this->createTask());
        $pending = $repo->findPending();
        $recordId = $pending[0]->id;

        $repo->markAsFailed($recordId, 'worker down');

        $this->assertCount(0, $repo->findPending());
        $all = $repo->all();
        $this->assertSame(TaskOutboxStatus::Failed, $all[0]->status);
        $this->assertSame('worker down', $all[0]->lastError);
    }

    public function testMarkAsFailedIncrementsAttempts(): void
    {
        $repo = new InMemoryTaskOutboxRepository();
        $repo->save($this->createTask());
        $pending = $repo->findPending();
        $recordId = $pending[0]->id;

        $this->assertSame(0, $pending[0]->attempts);

        $repo->markAsFailed($recordId, 'err');
        $all = $repo->all();

        $this->assertSame(1, $all[0]->attempts);
    }

    public function testAllReturnsAllRecordsRegardlessOfStatus(): void
    {
        $repo = new InMemoryTaskOutboxRepository();
        $repo->save($this->createTask('task-1'));
        $repo->save($this->createTask('task-2'));
        $pending = $repo->findPending();
        $repo->markAsDelivered($pending[0]->id);

        $all = $repo->all();

        $this->assertCount(2, $all);
    }

    public function testResetClearsAllRecords(): void
    {
        $repo = new InMemoryTaskOutboxRepository();
        $repo->save($this->createTask('task-1'));
        $repo->save($this->createTask('task-2'));

        $repo->reset();

        $this->assertCount(0, $repo->all());
        $this->assertCount(0, $repo->findPending());
    }

    public function testMarkAsDeliveredIsNoopForUnknownId(): void
    {
        $repo = new InMemoryTaskOutboxRepository();
        $repo->save($this->createTask('task-1'));

        $repo->markAsDelivered('non-existent-id');

        $pending = $repo->findPending();
        $this->assertCount(1, $pending);
        $this->assertSame(TaskOutboxStatus::Pending, $pending[0]->status);
    }

    public function testMarkAsFailedIsNoopForUnknownId(): void
    {
        $repo = new InMemoryTaskOutboxRepository();
        $repo->save($this->createTask('task-1'));

        $repo->markAsFailed('non-existent-id', 'some error');

        $pending = $repo->findPending();
        $this->assertCount(1, $pending);
        $this->assertSame(TaskOutboxStatus::Pending, $pending[0]->status);
    }
}
