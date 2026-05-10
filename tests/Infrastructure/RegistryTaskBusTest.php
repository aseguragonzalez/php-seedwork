<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\BackgroundTask;
use SeedWork\Application\TaskHandler;
use SeedWork\Application\TaskStatus;
use SeedWork\Infrastructure\RegistryTaskBus;

final class RegistryTaskBusTest extends TestCase
{
    public function testDispatchInvokesRegisteredHandler(): void
    {
        $task = $this->createTask('task-001', 'send-email');
        $handler = $this->createMock(TaskHandler::class);
        $handler->expects($this->once())->method('handle')->with($task);

        $bus = new RegistryTaskBus();
        $bus->register('send-email', $handler);

        $bus->dispatch($task);
    }

    public function testDispatchThrowsWhenNoHandlerRegistered(): void
    {
        $task = $this->createTask('task-001', 'unknown-type');
        $bus = new RegistryTaskBus();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No handler for task type: unknown-type');

        $bus->dispatch($task);
    }

    public function testDispatchUsesCorrectHandlerForMultipleTypes(): void
    {
        $task1 = $this->createTask('task-1', 'send-email');
        $task2 = $this->createTask('task-2', 'send-sms');
        $handler1 = $this->createMock(TaskHandler::class);
        $handler1->expects($this->once())->method('handle')->with($task1);
        $handler2 = $this->createMock(TaskHandler::class);
        $handler2->expects($this->once())->method('handle')->with($task2);

        $bus = new RegistryTaskBus();
        $bus->register('send-email', $handler1);
        $bus->register('send-sms', $handler2);

        $bus->dispatch($task1);
        $bus->dispatch($task2);
    }

    private function createTask(string $id, string $type): BackgroundTask
    {
        return new readonly class ($id, $type) extends BackgroundTask {
            public function __construct(string $id, string $type)
            {
                parent::__construct(
                    id: $id,
                    type: $type,
                    payload: ['key' => 'value'],
                    status: TaskStatus::Pending,
                    scheduledAt: new \DateTimeImmutable(),
                    attempts: 0,
                    maxAttempts: 3,
                    correlationId: 'corr-001'
                );
            }
        };
    }
}
