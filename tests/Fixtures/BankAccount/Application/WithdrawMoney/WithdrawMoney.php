<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\WithdrawMoney;

use SeedWork\Application\CommandHandler;

/**
 * Application service that handles WithdrawMoneyCommand.
 *
 * @extends CommandHandler<WithdrawMoneyCommand>
 */
interface WithdrawMoney extends CommandHandler
{
}
