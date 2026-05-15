<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\Maybe;
use SeedWork\Application\QueryBus;
use SeedWork\Application\ValidationErrors;
use SeedWork\Infrastructure\ValidationQueryBus;
use Tests\Fixtures\TestQuery;
use Tests\Fixtures\TestQueryResult;

final class ValidationQueryBusTest extends TestCase
{
    public function testAskDelegatesToInnerBusWhenValidationPasses(): void
    {
        $query = new TestQuery('some-id');
        $expectedMaybe = Maybe::just(new TestQueryResult('some-id'));
        $innerBus = $this->createMock(QueryBus::class);
        $innerBus->expects($this->once())
            ->method('ask')
            ->with($query)
            ->willReturn($expectedMaybe);

        $bus = new ValidationQueryBus($innerBus);
        $actual = $bus->ask($query);

        self::assertSame($expectedMaybe, $actual);
    }

    public function testAskThrowsAndSkipsInnerBusWhenValidationFails(): void
    {
        $query = new TestQuery('');
        $innerBus = $this->createMock(QueryBus::class);
        $innerBus->expects($this->never())->method('ask');

        $bus = new ValidationQueryBus($innerBus);

        $this->expectException(ValidationErrors::class);
        $bus->ask($query);
    }
}
