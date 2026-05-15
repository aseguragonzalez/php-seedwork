<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use SeedWork\Domain\Exceptions\NotFoundResource;
use Tests\Fixtures\TestAggregate;
use Tests\Fixtures\TestAggregateObtainer;
use Tests\Fixtures\TestId;
use Tests\Fixtures\TestRepository;

final class AggregateObtainerTest extends TestCase
{
    public function testObtainReturnsAggregateWhenFound(): void
    {
        $aggregate = TestAggregate::create();
        $repository = new TestRepository();
        $repository->save($aggregate);
        $obtainer = new TestAggregateObtainer($repository);

        $result = $obtainer->obtain($aggregate->id);

        $this->assertInstanceOf(TestAggregate::class, $result);
        $this->assertTrue($result->id->equals($aggregate->id));
    }

    public function testObtainThrowsNotFoundResourceWhenNotFound(): void
    {
        $repository = new TestRepository();
        $obtainer = new TestAggregateObtainer($repository);
        $id = TestId::fromString('test-nonexistent');

        $this->expectException(NotFoundResource::class);
        $this->expectExceptionMessage("Resource 'TestAggregate' not found for id 'test-nonexistent'");

        $obtainer->obtain($id);
    }

    public function testObtainThrowsNotFoundResourceWithResourceNameInMessage(): void
    {
        $repository = new TestRepository();
        $obtainer = new TestAggregateObtainer($repository);

        $exception = null;
        try {
            $obtainer->obtain(TestId::fromString('test-xyz'));
        } catch (NotFoundResource $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception);
        $this->assertStringContainsString('TestAggregate', $exception->getMessage());
    }
}
