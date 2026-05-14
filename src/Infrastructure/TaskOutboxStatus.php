<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

/**
 * Lifecycle status of a {@see TaskOutboxRecord}.
 *
 * @see TaskOutboxRecord The record that carries this status.
 * @see TaskOutboxRepository Repository that transitions records between statuses.
 */
enum TaskOutboxStatus: string
{
    case Pending   = 'pending';
    case Delivered = 'delivered';
    case Failed    = 'failed';
}
