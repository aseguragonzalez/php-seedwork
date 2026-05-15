<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Domain\DomainEvent;

final readonly class AnotherTestEvent extends DomainEvent
{
    private function __construct(
        TestEventId $id,
        \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id, $createdAt);
    }

    public static function create(
        ?TestEventId $id = null,
        ?\DateTimeImmutable $createdAt = null,
    ): self {
        return new self(
            $id ?? TestEventId::create(),
            $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
    }
}
