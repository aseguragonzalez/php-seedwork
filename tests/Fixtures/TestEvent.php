<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Domain\DomainEvent;

final readonly class TestEvent extends DomainEvent
{
    private function __construct(
        public string $name,
        TestEventId $id,
        \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id, $createdAt);
    }

    public static function create(
        string $name = 'test.event',
        ?TestEventId $id = null,
        ?\DateTimeImmutable $createdAt = null,
    ): self {
        return new self(
            $name,
            $id ?? TestEventId::create(),
            $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
    }
}
