<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\OpenAccount;

use SeedWork\Application\CommandHandler;

/**
 * Application service that handles OpenAccountCommand.
 *
 * @extends CommandHandler<OpenAccountCommand>
 */
interface OpenAccount extends CommandHandler
{
}
