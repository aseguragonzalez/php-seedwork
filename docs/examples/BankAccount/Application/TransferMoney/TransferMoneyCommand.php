<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\TransferMoney;

use SeedWork\Application\Command;

final readonly class TransferMoneyCommand extends Command
{
    public function __construct(
        public string $fromAccountId,
        public string $toAccountId,
        public int $amount,
        public string $currency
    ) {
        parent::__construct();
    }
}
