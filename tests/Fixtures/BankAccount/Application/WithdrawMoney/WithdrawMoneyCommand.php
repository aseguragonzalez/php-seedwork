<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\WithdrawMoney;

use SeedWork\Application\Command;

final readonly class WithdrawMoneyCommand extends Command
{
    public function __construct(
        public string $accountId,
        public int $amount,
        public string $currency
    ) {
        parent::__construct();
    }
}
