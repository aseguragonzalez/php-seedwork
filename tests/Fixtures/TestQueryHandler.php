<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Application\Maybe;
use SeedWork\Application\Query;
use SeedWork\Application\QueryHandler;

/**
 * @implements QueryHandler<TestQuery>
 */
final readonly class TestQueryHandler implements QueryHandler
{
    public function __construct(private TestRepository $repository)
    {
    }

    public function handle(Query $query): Maybe
    {
        $aggregate = $this->repository->findBy(TestId::fromString($query->id));
        if ($aggregate === null) {
            /** @var Maybe<mixed> $nothing */
            $nothing = Maybe::nothing();
            return $nothing;
        }

        return Maybe::just(new TestQueryResult($aggregate->id->value));
    }
}
