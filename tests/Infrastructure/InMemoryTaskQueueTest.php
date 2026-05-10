<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\BackgroundTask;
use SeedWork\Application\TaskStatus;
use SeedWork\Infrastructure\InMemoryTaskQueue;

final class InMemoryTaskQueueTest extends TestCase
{
    public function testEnqueueAndDequeue(): void
    {
        $queue = new InMemoryTaskQueue();
        $task = $this->createTask('task-1', 'send-email');

        $queue->enqueue($task);
        $dequeued = $queue->dequeue();

        $this->assertNotNull($dequeued);
        $this->assertSame('task-1', $dequeued->id);
        $this->assertSame(TaskStatus::Running, $dequeued->status);
    }

    public function testDequeueReturnsNullWhenEmpty(): void
    {
        $queue = new InMemoryTaskQueue();

        $result = $queue->dequeue();

        $this->assertNull($result);
    }

    public function testDequeueClaimsAtomically(): void
    {
        $queue = new InMemoryTaskQueue();
        $queue->enqueue($this->createTask('task-1'));

        $first = $queue->dequeue();
        $second = $queue->dequeue();

        $this->assertNotNull($first);
        $this->assertNull($second);
    }

    public function testAckMarksTaskAsCompleted(): void
    {
        $queue = new InMemoryTaskQueue();
        $task = $this->createTask('task-1');
        $queue->enqueue($task);
        $dequeued = $queue->dequeue();
        $this->assertNotNull($dequeued);

        $queue->ack($dequeued->id);
        $found = $queue->findById('task-1');

        $this->assertNotNull($found);
        $this->assertSame(TaskStatus::Completed, $found->status);
    }

    public function testNackReenqueuesWhenBelowMaxAttempts(): void
    {
        $queue = new InMemoryTaskQueue();
        $task = $this->createTask('task-1', 'send-email', 3);
        $queue->enqueue($task);
        $dequeued = $queue->dequeue();
        $this->assertNotNull($dequeued);

        $queue->nack($dequeued->id, 'temporary failure');

        $requeued = $queue->dequeue();
        $this->assertNotNull($requeued);
        $this->assertSame(TaskStatus::Running, $requeued->status);
        $this->assertSame(1, $requeued->attempts);
    }

    public function testNackMarksAsFailedAtMaxAttempts(): void
    {
        $queue = new InMemoryTaskQueue();
        $task = $this->createTask('task-1', 'send-email', 1);
        $queue->enqueue($task);
        $dequeued = $queue->dequeue();
        $this->assertNotNull($dequeued);

        $queue->nack($dequeued->id, 'permanent failure');

        $found = $queue->findById('task-1');
        $this->assertNotNull($found);
        $this->assertSame(TaskStatus::Failed, $found->status);
        $this->assertNull($queue->dequeue());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $queue = new InMemoryTaskQueue();

        $result = $queue->findById('nonexistent');

        $this->assertNull($result);
    }

    public function testFindByIdReturnsTaskById(): void
    {
        $queue = new InMemoryTaskQueue();
        $task = $this->createTask('task-1');
        $queue->enqueue($task);

        $found = $queue->findById('task-1');

        $this->assertNotNull($found);
        $this->assertSame('task-1', $found->id);
    }

    private function createTask(string $id, string $type = 'send-email', int $maxAttempts = 3): BackgroundTask
    {
        return new readonly class ($id, $type, $maxAttempts) extends BackgroundTask {
            public function __construct(string $id, string $type, int $maxAttempts)
            {
                parent::__construct(
                    id: $id,
                    type: $type,
                    payload: [],
                    status: TaskStatus::Pending,
                    scheduledAt: new \DateTimeImmutable(),
                    attempts: 0,
                    maxAttempts: $maxAttempts,
                    correlationId: 'corr-001'
                );
            }
        };
    }
}
