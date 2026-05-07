<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain;

use SeedWork\Domain\AggregateObtainer;
use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\Repositories\BankAccountRepository;

/**
 * @extends AggregateObtainer<BankAccount>
 */
final readonly class BankAccountObtainer extends AggregateObtainer
{
    public function __construct(BankAccountRepository $repository)
    {
        parent::__construct($repository, 'BankAccount');
    }
}
