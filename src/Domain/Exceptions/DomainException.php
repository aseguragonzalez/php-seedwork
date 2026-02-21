<?php

declare(strict_types=1);

namespace SeedWork\Domain\Exceptions;

abstract class DomainException extends \Exception
{
    protected function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
