<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\BackgroundTask;
use SeedWork\Application\TaskHandler;
use SeedWork\Infrastructure\InMemoryTaskScheduler;

final class InMemoryTaskSchedulerTest extends TestCase
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

    public function testScheduleAddsTaskToScheduledList(): void
    {
        $scheduler = new InMemoryTaskScheduler();
        $task = $this->createTask();

        $scheduler->schedule($task);

        $this->assertCount(1, $scheduler->scheduled());
        $this->assertSame($task, $scheduler->scheduled()[0]);
    }

    public function testScheduledReturnsEmptyArrayInitially(): void
    {
        $scheduler = new InMemoryTaskScheduler();

        $this->assertSame([], $scheduler->scheduled());
    }

    public function testExecuteScheduledInvokesRegisteredHandlerForMatchingType(): void
    {
        $task = $this->createTask('task-1', 'domain.action');
        $handler = $this->createMock(TaskHandler::class);
        $handler->expects($this->once())->method('handle')->with($task);

        $scheduler = new InMemoryTaskScheduler();
        $scheduler->register('domain.action', $handler);
        $scheduler->schedule($task);

        $scheduler->executeScheduled();
    }

    public function testExecuteScheduledSkipsTasksWithNoRegisteredHandler(): void
    {
        $task = $this->createTask('task-1', 'domain.unregistered');
        $handler = $this->createMock(TaskHandler::class);
        $handler->expects($this->never())->method('handle');

        $scheduler = new InMemoryTaskScheduler();
        $scheduler->register('domain.other', $handler);
        $scheduler->schedule($task);

        $scheduler->executeScheduled(); // should not throw
        $this->addToAssertionCount(1);
    }

    public function testExecuteScheduledDispatchesMultipleTasks(): void
    {
        $task1 = $this->createTask('task-1', 'domain.action_a');
        $task2 = $this->createTask('task-2', 'domain.action_b');
        $handler1 = $this->createMock(TaskHandler::class);
        $handler1->expects($this->once())->method('handle')->with($task1);
        $handler2 = $this->createMock(TaskHandler::class);
        $handler2->expects($this->once())->method('handle')->with($task2);

        $scheduler = new InMemoryTaskScheduler();
        $scheduler->register('domain.action_a', $handler1);
        $scheduler->register('domain.action_b', $handler2);
        $scheduler->schedule($task1);
        $scheduler->schedule($task2);

        $scheduler->executeScheduled();
    }

    public function testResetClearsScheduledTasks(): void
    {
        $scheduler = new InMemoryTaskScheduler();
        $scheduler->schedule($this->createTask());

        $scheduler->reset();

        $this->assertSame([], $scheduler->scheduled());
    }

    public function testResetClearsHandlerRegistry(): void
    {
        $task = $this->createTask('task-1', 'domain.action');
        $handler = $this->createMock(TaskHandler::class);
        $handler->expects($this->never())->method('handle');

        $scheduler = new InMemoryTaskScheduler();
        $scheduler->register('domain.action', $handler);
        $scheduler->reset();
        $scheduler->schedule($task);

        $scheduler->executeScheduled(); // handler was cleared — should not call
    }
}
