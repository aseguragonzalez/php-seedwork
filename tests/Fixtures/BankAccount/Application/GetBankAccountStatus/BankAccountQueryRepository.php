<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\GetBankAccountStatus;

use SeedWork\Application\QueryRepository;

/**
 * Query repository for BankAccountProjection.
 *
 * @extends QueryRepository<BankAccountProjection>
 */
interface BankAccountQueryRepository extends QueryRepository
{
}
