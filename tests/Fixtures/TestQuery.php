<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Application\Query;
use SeedWork\Application\ValidationError;
use SeedWork\Application\ValidationErrors;

final readonly class TestQuery extends Query
{
    public function __construct(public string $id = 'test-id')
    {
        parent::__construct();
    }

    public function validate(): void
    {
        if (empty($this->id)) {
            throw new ValidationErrors([
                new ValidationError('id', 'Id is required.'),
            ]);
        }
    }
}
