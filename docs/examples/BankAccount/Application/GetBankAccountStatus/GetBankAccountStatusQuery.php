<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\GetBankAccountStatus;

use SeedWork\Application\Query;

final readonly class GetBankAccountStatusQuery extends Query
{
    public function __construct(
        public string $accountId
    ) {
        parent::__construct();
    }
}
