<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Infrastructure\InMemoryRepository;

/**
 * @extends InMemoryRepository<TestAggregate>
 */
final class TestRepository extends InMemoryRepository
{
}
