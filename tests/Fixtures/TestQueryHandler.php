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

    /**
     * @param TestQuery $query
     * @return Maybe<TestQueryResult>
     */
    public function handle(Query $query): Maybe
    {
        $aggregate = $this->repository->findById(TestId::fromString($query->id));
        if ($aggregate === null) {
            return Maybe::nothing();
        }

        return Maybe::just(new TestQueryResult($aggregate->id->value));
    }
}
