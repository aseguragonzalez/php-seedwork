<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Application\Command;

final readonly class AnotherTestCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }
}
