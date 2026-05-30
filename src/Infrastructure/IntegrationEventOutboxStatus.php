<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

/**
 * Lifecycle status of an {@see IntegrationEventOutboxRecord}.
 *
 * @see IntegrationEventOutboxRecord The record that carries this status.
 * @see IntegrationEventOutboxRepository Repository that transitions records between statuses.
 */
enum IntegrationEventOutboxStatus: string
{
    case Pending = 'pending';
    case Published = 'published';
    case Failed = 'failed';
}
