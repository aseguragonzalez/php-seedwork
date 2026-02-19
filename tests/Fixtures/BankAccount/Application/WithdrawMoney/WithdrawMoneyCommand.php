<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\WithdrawMoney;

use Seedwork\Application\Command;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final readonly class WithdrawMoneyCommand extends Command
{
    public function __construct(
        public BankAccountId $accountId,
        public Money $amount
    ) {
        parent::__construct();
    }
}
