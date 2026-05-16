<?php

declare(strict_types=1);

namespace Tests\Fixtures;

final readonly class TestId
{
    public static function create(): self
    {
        return new self('test-' . uniqid('', true));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function __construct(public string $value)
    {
        if (empty($this->value)) {
            throw new TestDomainException('TestId cannot be empty.');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
