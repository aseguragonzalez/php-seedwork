<?php

declare(strict_types=1);

namespace Tests\Domain\Exceptions;

use PHPUnit\Framework\TestCase;
use SeedWork\Domain\Exceptions\DomainException;
use SeedWork\Domain\Exceptions\NotFoundResource;
use Tests\Fixtures\TestId;

final class NotFoundResourceTest extends TestCase
{
    public function testExceptionHasExpectedMessageWhenIdProvided(): void
    {
        $id = TestId::fromString('test-123');
        $exception = new NotFoundResource('TestAggregate', $id);

        $this->assertSame("Resource 'TestAggregate' not found for id 'test-123'", $exception->getMessage());
    }

    public function testExceptionHasExpectedMessageWhenIdOmitted(): void
    {
        $exception = new NotFoundResource('Order');

        $this->assertSame("Resource 'Order' not found", $exception->getMessage());
    }

    public function testExceptionExtendsDomainException(): void
    {
        $exception = new NotFoundResource('TestAggregate');

        $this->assertInstanceOf(DomainException::class, $exception);
    }

    public function testExceptionPreservesCode(): void
    {
        $exception = new NotFoundResource('TestAggregate', null, 404);

        $this->assertSame(404, $exception->getCode());
    }
}
