<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Domain\Exceptions\ValueException;
use SeedWork\Domain\ValueObject;

final readonly class TestValueObject extends ValueObject
{
    public function __construct(public string $value)
    {
        parent::__construct();
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    protected function validate(): void
    {
        if (empty($this->value)) {
            throw new ValueException('Value cannot be empty.');
        }
    }
}
