<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\GetBankAccountStatus;

use Seedwork\Application\Query;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;

final readonly class GetBankAccountStatusQuery extends Query
{
    public function __construct(
        public BankAccountId $accountId
    ) {
        parent::__construct();
    }
}
