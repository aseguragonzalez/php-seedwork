<?php

declare(strict_types=1);

namespace Tests\Fixtures;

final class TestDomainException extends \DomainException
{
    public function __construct(string $message = 'Test domain error.')
    {
        parent::__construct($message);
    }
}
