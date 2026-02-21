<?php

declare(strict_types=1);

namespace SeedWork\Domain\Exceptions;

final class ValueException extends DomainException
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
