<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Domain\AggregateObtainer;

/**
 * @extends AggregateObtainer<TestAggregate>
 */
final readonly class TestAggregateObtainer extends AggregateObtainer
{
    public function __construct(TestRepository $repository)
    {
        parent::__construct($repository, 'TestAggregate');
    }
}
