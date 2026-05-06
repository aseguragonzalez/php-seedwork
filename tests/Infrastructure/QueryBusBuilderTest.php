<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\QueryBus;
use SeedWork\Application\QueryValidator;
use SeedWork\Infrastructure\QueryBusBuilder;
use SeedWork\Infrastructure\ValidationQueryBus;

final class QueryBusBuilderTest extends TestCase
{
    public function testBuildReturnsBaseQueryBusWhenNoDecoratorsAdded(): void
    {
        $innerBus = $this->createMock(QueryBus::class);

        $result = QueryBusBuilder::from($innerBus)->build();

        self::assertSame($innerBus, $result);
    }

    public function testWithValidationWrapsCurrentBus(): void
    {
        $innerBus = $this->createMock(QueryBus::class);
        $validator = $this->createMock(QueryValidator::class);

        $result = QueryBusBuilder::from($innerBus)->withValidation($validator)->build();

        self::assertInstanceOf(ValidationQueryBus::class, $result);
    }

    public function testChainReturnsSameBuilderInstance(): void
    {
        $innerBus = $this->createMock(QueryBus::class);
        $validator = $this->createMock(QueryValidator::class);
        $builder = QueryBusBuilder::from($innerBus);

        $same = $builder->withValidation($validator);

        self::assertSame($builder, $same);
    }
}
