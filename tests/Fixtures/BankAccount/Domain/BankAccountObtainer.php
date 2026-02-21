<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain;

use Seedwork\Domain\AggregateObtainer;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;

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
