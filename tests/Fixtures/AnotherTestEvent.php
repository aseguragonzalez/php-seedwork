<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Domain\DomainEvent;

final readonly class AnotherTestEvent extends DomainEvent
{
    private function __construct(
        string $id,
        \DateTimeImmutable $occurredAt,
    ) {
        parent::__construct($id, $occurredAt);
    }

    public static function create(
        ?string $id = null,
        ?\DateTimeImmutable $occurredAt = null,
    ): self {
        return new self(
            $id ?? 'evt-' . uniqid('', true),
            $occurredAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
    }
}
