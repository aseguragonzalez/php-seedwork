<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Domain\Exceptions\DomainException;

final class TestDomainException extends DomainException
{
    public function __construct(string $message = 'Test domain error.')
    {
        parent::__construct($message);
    }
}
