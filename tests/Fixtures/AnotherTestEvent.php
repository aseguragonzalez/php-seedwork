<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Domain\DomainEvent;

final readonly class AnotherTestEvent extends DomainEvent
{
    private function __construct(
        string $id,
        \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id, $createdAt);
    }

    public static function create(
        ?string $id = null,
        ?\DateTimeImmutable $createdAt = null,
    ): self {
        return new self(
            $id ?? 'evt-' . uniqid('', true),
            $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
    }
}
