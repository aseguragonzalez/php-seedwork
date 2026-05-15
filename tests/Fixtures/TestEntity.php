<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Domain\Entity;
use SeedWork\Domain\Exceptions\ValueException;

/**
 * @extends Entity<TestId>
 */
final readonly class TestEntity extends Entity
{
    private function __construct(
        TestId $id,
        public \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id);
    }

    protected function validate(): void
    {
        if ($this->createdAt > new \DateTimeImmutable('now')) {
            throw new ValueException('TestEntity createdAt cannot be in the future.');
        }
    }

    public static function create(?TestId $id = null, ?\DateTimeImmutable $createdAt = null): self
    {
        return new self(
            $id ?? TestId::create(),
            $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
    }

    public static function build(TestId $id, \DateTimeImmutable $createdAt): self
    {
        return new self($id, $createdAt);
    }
}
