<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\TransferMoney;

use Seedwork\Application\Command;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final readonly class TransferMoneyCommand extends Command
{
    public function __construct(
        public BankAccountId $fromAccountId,
        public BankAccountId $toAccountId,
        public Money $amount
    ) {
        parent::__construct();
    }
}
