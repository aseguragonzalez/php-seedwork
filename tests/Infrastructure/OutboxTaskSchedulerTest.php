<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\BackgroundTask;
use SeedWork\Infrastructure\OutboxTaskScheduler;
use SeedWork\Infrastructure\TaskOutboxRepository;

final class OutboxTaskSchedulerTest extends TestCase
{
    private function createTask(string $id = 'task-001'): BackgroundTask
    {
        return new readonly class ($id) extends BackgroundTask {
            public function __construct(string $id)
            {
                parent::__construct(
                    id: $id,
                    type: 'domain.test_task',
                    payload: ['key' => 'value'],
                    correlationId: 'corr-001'
                );
            }
        };
    }

    public function testScheduleDelegatesToRepository(): void
    {
        $task = $this->createTask();
        $repository = $this->createMock(TaskOutboxRepository::class);
        $repository->expects($this->once())->method('save')->with($task);
        $scheduler = new OutboxTaskScheduler($repository);

        $scheduler->schedule($task);
    }

    public function testScheduleCallsSaveForEachTask(): void
    {
        $task1 = $this->createTask('task-001');
        $task2 = $this->createTask('task-002');
        $repository = $this->createMock(TaskOutboxRepository::class);
        $repository->expects($this->exactly(2))->method('save');
        $scheduler = new OutboxTaskScheduler($repository);

        $scheduler->schedule($task1);
        $scheduler->schedule($task2);
    }
}
