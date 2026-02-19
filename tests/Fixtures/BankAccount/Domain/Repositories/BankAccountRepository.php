<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Repositories;

use Seedwork\Domain\Repository;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;

/**
 * @extends Repository<BankAccount>
 */
interface BankAccountRepository extends Repository
{
}
