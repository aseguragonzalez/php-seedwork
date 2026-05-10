<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Lifecycle status of a {@see BackgroundTask}.
 *
 * @see BackgroundTask The task that carries this status.
 * @see TaskQueue      Queue that transitions tasks between statuses.
 */
enum TaskStatus: string
{
    case Pending   = 'pending';
    case Running   = 'running';
    case Completed = 'completed';
    case Failed    = 'failed';
}
