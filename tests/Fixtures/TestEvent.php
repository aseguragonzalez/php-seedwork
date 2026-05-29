<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Domain\DomainEvent;

final readonly class TestEvent extends DomainEvent
{
    private function __construct(
        public string $name,
        string $id,
        string $aggregateId,
        \DateTimeImmutable $occurredAt,
    ) {
        parent::__construct($id, $aggregateId, $occurredAt);
    }

    public static function create(
        string $name = 'test.event',
        ?string $id = null,
        ?string $aggregateId = null,
        ?\DateTimeImmutable $occurredAt = null,
    ): self {
        return new self(
            $name,
            $id ?? 'evt-'.uniqid('', true),
            $aggregateId ?? 'agg-test',
            $occurredAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
    }
}
