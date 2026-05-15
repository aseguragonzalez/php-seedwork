<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Domain\EntityId;
use SeedWork\Domain\Exceptions\ValueException;

final readonly class TestId extends EntityId
{
    public static function create(): self
    {
        return new self('test-' . uniqid('', true));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function __construct(string $value)
    {
        parent::__construct($value);
    }

    protected function validate(): void
    {
        if (empty($this->value)) {
            throw new ValueException('TestId cannot be empty.');
        }
    }
}
