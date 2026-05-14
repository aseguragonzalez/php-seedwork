<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\Maybe;
use SeedWork\Application\QueryBus;
use SeedWork\Infrastructure\QueryBusBuilder;
use SeedWork\Infrastructure\RegistryQueryBus;
use SeedWork\Infrastructure\ValidationQueryBus;

final class QueryBusBuilderTest extends TestCase
{
    public function testBuildWithNoStepsReturnsRegistryDirectly(): void
    {
        $registry = new RegistryQueryBus();

        $result = (new QueryBusBuilder($registry))->build();

        self::assertSame($registry, $result);
    }

    public function testRegistryReturnsInjectedInstance(): void
    {
        $registry = new RegistryQueryBus();

        $builder = new QueryBusBuilder($registry);

        self::assertSame($registry, $builder->registry());
    }

    public function testRegistryRemainsTheSameAfterAddingSteps(): void
    {
        $registry = new RegistryQueryBus();
        $builder = new QueryBusBuilder($registry);

        $builder->withValidation();

        self::assertSame($registry, $builder->registry());
    }

    public function testWithValidationProducesValidationQueryBus(): void
    {
        $result = (new QueryBusBuilder(new RegistryQueryBus()))
            ->withValidation()
            ->build();

        self::assertInstanceOf(ValidationQueryBus::class, $result);
    }

    public function testFirstStepAddedBecomesOutermostDecorator(): void
    {
        $inner = $this->createMock(QueryBus::class);
        $inner->method('ask')->willReturn(Maybe::nothing());

        $result = (new QueryBusBuilder(new RegistryQueryBus()))
            ->withValidation()
            ->use(fn (QueryBus $bus): QueryBus => $inner)
            ->build();

        self::assertInstanceOf(ValidationQueryBus::class, $result);
    }

    public function testUseAppliesCustomMiddleware(): void
    {
        $customBus = $this->createMock(QueryBus::class);
        $customBus->method('ask')->willReturn(Maybe::nothing());

        $result = (new QueryBusBuilder(new RegistryQueryBus()))
            ->use(fn (QueryBus $inner): QueryBus => $customBus)
            ->build();

        self::assertSame($customBus, $result);
    }

    public function testChainReturnsSameBuilderInstance(): void
    {
        $builder = new QueryBusBuilder(new RegistryQueryBus());

        $same = $builder->withValidation();

        self::assertSame($builder, $same);
    }
}
