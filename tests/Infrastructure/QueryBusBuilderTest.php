<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\Maybe;
use SeedWork\Application\QueryBus;
use SeedWork\Infrastructure\QueryBusBuilder;
use SeedWork\Infrastructure\RegistryQueryBus;

/**
 * @internal
 *
 * @coversNothing
 */
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
        $customBus = $this->createStub(QueryBus::class);

        $builder->use(fn (QueryBus $inner): QueryBus => $customBus);

        self::assertSame($registry, $builder->registry());
    }

    public function testUseAppliesCustomMiddleware(): void
    {
        $customBus = $this->createStub(QueryBus::class);
        $customBus->method('ask')->willReturn(Maybe::nothing());

        $result = (new QueryBusBuilder(new RegistryQueryBus()))
            ->use(fn (QueryBus $inner): QueryBus => $customBus)
            ->build()
        ;

        self::assertSame($customBus, $result);
    }

    public function testFirstStepAddedBecomesOutermostDecorator(): void
    {
        $outerBus = $this->createStub(QueryBus::class);
        $innerBus = $this->createStub(QueryBus::class);

        $result = (new QueryBusBuilder(new RegistryQueryBus()))
            ->use(fn (QueryBus $bus): QueryBus => $outerBus)
            ->use(fn (QueryBus $bus): QueryBus => $innerBus)
            ->build()
        ;

        self::assertSame($outerBus, $result);
    }

    public function testChainReturnsSameBuilderInstance(): void
    {
        $builder = new QueryBusBuilder(new RegistryQueryBus());
        $customBus = $this->createStub(QueryBus::class);

        $same = $builder->use(fn (QueryBus $inner): QueryBus => $customBus);

        self::assertSame($builder, $same);
    }
}
