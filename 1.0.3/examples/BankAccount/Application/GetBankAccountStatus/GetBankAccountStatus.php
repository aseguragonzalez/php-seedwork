<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\GetBankAccountStatus;

use SeedWork\Application\QueryHandler;

/**
 * Application service that handles GetBankAccountStatusQuery and returns Maybe<BankAccountStatusResult>.
 *
 * @extends QueryHandler<GetBankAccountStatusQuery>
 */
interface GetBankAccountStatus extends QueryHandler {}
