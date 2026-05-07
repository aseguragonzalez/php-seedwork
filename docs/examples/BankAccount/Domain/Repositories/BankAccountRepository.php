<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Repositories;

use SeedWork\Domain\Repository;
use Examples\BankAccount\Domain\Entities\BankAccount;

/**
 * @extends Repository<BankAccount>
 */
interface BankAccountRepository extends Repository
{
}
