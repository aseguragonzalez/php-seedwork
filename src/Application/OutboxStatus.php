<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Lifecycle status of an {@see OutboxRecord}.
 *
 * @see OutboxRecord The record that carries this status.
 * @see OutboxRepository Repository that transitions records between statuses.
 */
enum OutboxStatus: string
{
    case Pending   = 'pending';
    case Published = 'published';
    case Failed    = 'failed';
}
