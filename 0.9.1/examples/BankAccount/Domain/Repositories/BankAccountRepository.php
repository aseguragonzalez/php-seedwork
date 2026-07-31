<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Repositories;

use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use SeedWork\Domain\Repository;

/**
 * @extends Repository<BankAccountId, BankAccount>
 */
interface BankAccountRepository extends Repository {}
