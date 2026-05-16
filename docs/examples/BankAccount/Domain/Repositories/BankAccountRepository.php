<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Repositories;

use SeedWork\Domain\Repository;
use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\Entities\BankAccountId;

/**
 * @extends Repository<BankAccountId, BankAccount>
 */
interface BankAccountRepository extends Repository
{
}
