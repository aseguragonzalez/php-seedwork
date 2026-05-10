<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\QueryBus;
use SeedWork\Infrastructure\QueryBusBuilder;
use SeedWork\Infrastructure\RegistryQueryBus;
use SeedWork\Infrastructure\ValidationQueryBus;

final class QueryBusBuilderTest extends TestCase
{
    public function testBuildReturnsBaseQueryBusWhenNoDecoratorsAdded(): void
    {
        $innerBus = $this->createStub(QueryBus::class);

        $result = QueryBusBuilder::from($innerBus)->build();

        self::assertSame($innerBus, $result);
    }

    public function testNewCreatesRegistryQueryBusAsDefault(): void
    {
        $result = QueryBusBuilder::new()->build();

        self::assertInstanceOf(RegistryQueryBus::class, $result);
    }

    public function testWithValidationWrapsCurrentBus(): void
    {
        $innerBus = $this->createStub(QueryBus::class);

        $result = QueryBusBuilder::from($innerBus)->withValidation()->build();

        self::assertInstanceOf(ValidationQueryBus::class, $result);
    }

    public function testChainReturnsSameBuilderInstance(): void
    {
        $innerBus = $this->createStub(QueryBus::class);
        $builder = QueryBusBuilder::from($innerBus);

        $same = $builder->withValidation();

        self::assertSame($builder, $same);
    }
}
