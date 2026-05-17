<?php

declare(strict_types=1);

namespace Tests\Application;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\ValidationErrors;
use Tests\Fixtures\AnotherTestQuery;
use Tests\Fixtures\TestQuery;

final class QueryTest extends TestCase
{
    public function testConstructionSucceedsWhenValidationPasses(): void
    {
        $query = new TestQuery('some-id');

        self::assertSame('some-id', $query->id);
    }

    public function testConstructionThrowsWhenValidationFails(): void
    {
        $this->expectException(ValidationErrors::class);

        new TestQuery('');
    }

    public function testDefaultValidateIsNoOp(): void
    {
        $query = new AnotherTestQuery();

        self::assertInstanceOf(AnotherTestQuery::class, $query);
    }
}
