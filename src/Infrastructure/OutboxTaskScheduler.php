<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\BackgroundTask;
use SeedWork\Application\TaskScheduler;

/**
 * {@see TaskScheduler} that persists tasks via the outbox pattern for reliable
 * async delivery. Each task is stored as a pending {@see TaskOutboxRecord}
 * in the {@see TaskOutboxRepository}.
 *
 * @see TaskOutboxRepository Repository that stores the task outbox records.
 * @see BackgroundTask       Tasks stored by this scheduler.
 */
final class OutboxTaskScheduler implements TaskScheduler
{
    public function __construct(
        private readonly TaskOutboxRepository $repository
    ) {}

    public function schedule(BackgroundTask $task): void
    {
        $this->repository->save($task);
    }
}
