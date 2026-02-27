<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\GetBankAccountStatus;

use SeedWork\Application\Query;

final readonly class GetBankAccountStatusQuery extends Query
{
    public function __construct(
        public string $accountId
    ) {
        parent::__construct();
    }
}
