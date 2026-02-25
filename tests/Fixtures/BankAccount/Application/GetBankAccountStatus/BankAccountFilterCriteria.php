<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\GetBankAccountStatus;

use SeedWork\Application\FilterCriteria;
use SeedWork\Application\FilterOperator;

/**
 * Filter criteria for bank account projections. Allowed fields: id, balanceAmount,
 * balance, currency.
 *
 * @extends FilterCriteria<mixed>
 */
final readonly class BankAccountFilterCriteria extends FilterCriteria
{
    private const ALLOWED_FIELDS = ['id', 'balanceAmount', 'balance', 'currency'];

    public function __construct(
        string $field,
        FilterOperator $operator,
        mixed $value,
    ) {
        parent::__construct($field, $operator, $value);
    }

    protected function validate(): void
    {
        if (!in_array($this->field, self::ALLOWED_FIELDS, true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid field "%s"; allowed: %s', $this->field, implode(', ', self::ALLOWED_FIELDS))
            );
        }
    }
}
