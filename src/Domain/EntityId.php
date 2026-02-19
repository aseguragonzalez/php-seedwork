<?php

declare(strict_types=1);

namespace Seedwork\Domain;

abstract readonly class EntityId
{
    protected function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function equals(EntityId $other): bool
    {
        return $this->value === $other->value;
    }
}
