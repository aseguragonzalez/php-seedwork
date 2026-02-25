<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Operators for {@see FilterCriteria}. Use in filter criteria; implementations
 * interpret the operator when applying filters.
 */
enum FilterOperator: string
{
    case EQ = 'eq';
    case NEQ = 'neq';
    case GT = 'gt';
    case GTE = 'gte';
    case LT = 'lt';
    case LTE = 'lte';
    case IN = 'in';
    case BETWEEN = 'between';
    case LIKE = 'like';
}
