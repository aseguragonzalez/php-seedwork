<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\Maybe;
use SeedWork\Application\QueryHandler;
use SeedWork\Infrastructure\RegistryQueryBus;
use Tests\Fixtures\TestAggregate;
use Tests\Fixtures\TestId;
use Tests\Fixtures\TestQuery;
use Tests\Fixtures\TestQueryHandler;
use Tests\Fixtures\TestQueryResult;
use Tests\Fixtures\TestRepository;

final class RegistryQueryBusTest extends TestCase
{
    public function testAskInvokesRegisteredHandlerAndReturnsMaybe(): void
    {
        $query = new TestQuery('some-id');
        $expectedMaybe = Maybe::just(new TestQueryResult('some-id'));
        $handler = $this->createStub(QueryHandler::class);
        $handler->method('handle')->willReturn($expectedMaybe);

        $bus = new RegistryQueryBus();
        $bus->register(TestQuery::class, $handler);

        $result = $bus->ask($query);

        $this->assertSame($expectedMaybe, $result);
    }

    public function testAskThrowsLogicExceptionWhenNoHandlerRegistered(): void
    {
        $query = new TestQuery('some-id');
        $bus = new RegistryQueryBus();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No handler for');

        $bus->ask($query);
    }

    public function testAskWithRealHandlerReturnsNothingWhenAggregateNotFound(): void
    {
        $repository = new TestRepository();
        $handler = new TestQueryHandler($repository);

        $bus = new RegistryQueryBus();
        $bus->register(TestQuery::class, $handler);

        $maybe = $bus->ask(new TestQuery(TestId::create()->value));

        $this->assertFalse($maybe->hasValue());
    }

    public function testAskWithRealHandlerReturnsJustWhenAggregateExists(): void
    {
        $repository = new TestRepository();
        $aggregate = TestAggregate::create();
        $repository->save($aggregate);

        $handler = new TestQueryHandler($repository);

        $bus = new RegistryQueryBus();
        $bus->register(TestQuery::class, $handler);

        $maybe = $bus->ask(new TestQuery($aggregate->id->value));

        $this->assertTrue($maybe->hasValue());
        $this->assertInstanceOf(TestQueryResult::class, $maybe->value());
    }
}
