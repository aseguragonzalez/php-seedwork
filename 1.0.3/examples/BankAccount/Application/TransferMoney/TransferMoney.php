<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\TransferMoney;

use SeedWork\Application\CommandHandler;

/**
 * Application service that handles TransferMoneyCommand.
 *
 * @extends CommandHandler<TransferMoneyCommand>
 */
interface TransferMoney extends CommandHandler {}
