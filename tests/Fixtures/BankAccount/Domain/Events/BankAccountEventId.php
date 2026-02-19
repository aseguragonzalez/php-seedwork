<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Events;

use Seedwork\Domain\EventId;

final readonly class BankAccountEventId extends EventId
{
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function __construct(string $value)
    {
        parent::__construct($value);
    }
}
