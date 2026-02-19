<?php

declare(strict_types=1);

namespace Seedwork\Application;

/**
 * @template T of Query
 * @template R of QueryResult
 */
interface QueryHandler
{
    /**
     * @param T $query
     * @return R
     */
    public function handle(Query $query): QueryResult;
}
