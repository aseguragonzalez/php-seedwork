<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\TestAggregate;
use Tests\Fixtures\TestId;
use Tests\Fixtures\TestRepository;

final class InMemoryRepositoryTest extends TestCase
{
    public function testFindByReturnsNullWhenNotFound(): void
    {
        $repo = new TestRepository();

        $result = $repo->findById(TestId::create());

        $this->assertNull($result);
    }

    public function testSaveAndFindByHit(): void
    {
        $repo = new TestRepository();
        $aggregate = TestAggregate::create();

        $repo->save($aggregate);
        $result = $repo->findById($aggregate->id);

        $this->assertNotNull($result);
        $this->assertEquals($aggregate->id, $result->id);
    }

    public function testDeleteByRemovesAggregate(): void
    {
        $repo = new TestRepository();
        $aggregate = TestAggregate::create();
        $repo->save($aggregate);

        $repo->deleteById($aggregate->id);

        $this->assertNull($repo->findById($aggregate->id));
    }

    public function testSaveOverwritesExistingAggregate(): void
    {
        $repo = new TestRepository();
        $aggregate = TestAggregate::create();
        $repo->save($aggregate);

        // Save the same ID again (simulating an update)
        $repo->save($aggregate);

        $result = $repo->findById($aggregate->id);
        $this->assertNotNull($result);
    }
}
