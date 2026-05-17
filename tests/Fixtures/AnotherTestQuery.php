<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Application\Query;

final readonly class AnotherTestQuery extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
}
