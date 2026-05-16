<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Testing\InMemoryRepository;

/**
 * @extends InMemoryRepository<TestId, TestAggregate>
 */
final class TestRepository extends InMemoryRepository
{
}
