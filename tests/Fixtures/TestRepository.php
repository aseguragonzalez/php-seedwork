<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Testing\InMemoryRepository;

/**
 * @extends InMemoryRepository<TestAggregate>
 */
final class TestRepository extends InMemoryRepository
{
}
