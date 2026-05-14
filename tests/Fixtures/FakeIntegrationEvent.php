<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Application\IntegrationEvent;

/**
 * Concrete IntegrationEvent fixture for tests.
 */
final readonly class FakeIntegrationEvent extends IntegrationEvent
{
    public function __construct(string $eventId = 'evt-001')
    {
        parent::__construct(
            id: $eventId,
            type: 'test.fake_event',
            version: '1.0',
            aggregateId: 'agg-001',
            occurredAt: new \DateTimeImmutable('2024-01-01T00:00:00Z'),
            payload: ['key' => 'value'],
            correlationId: 'corr-001'
        );
    }
}
