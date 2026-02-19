<?php

declare(strict_types=1);

namespace Seedwork\Domain;

abstract readonly class ValueObject
{
    protected function __construct()
    {
        $this->validate();
    }

    abstract public function equals(ValueObject $other): bool;

    abstract protected function validate(): void;
}
