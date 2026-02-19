<?php

declare(strict_types=1);

namespace Seedwork\Domain;

abstract readonly class EventId
{
    protected function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(EventId $other): bool
    {
        return $this->value === $other->value;
    }
}
