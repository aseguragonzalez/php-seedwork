<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\GetBankAccountStatus;

use SeedWork\Application\QueryHandler;

/**
 * Application service that handles GetBankAccountStatusQuery and returns BankAccountStatusResult.
 *
 * @extends QueryHandler<GetBankAccountStatusQuery, BankAccountStatusResult>
 */
interface GetBankAccountStatus extends QueryHandler
{
}
