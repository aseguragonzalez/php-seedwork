<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\DepositMoney;

use Seedwork\Application\CommandHandler;

/**
 * Application service that handles DepositMoneyCommand.
 *
 * @extends CommandHandler<DepositMoneyCommand>
 */
interface DepositMoney extends CommandHandler
{
}
