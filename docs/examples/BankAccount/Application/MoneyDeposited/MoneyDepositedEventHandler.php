<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\MoneyDeposited;

use SeedWork\Application\DomainEventHandler;
use Examples\BankAccount\Domain\Events\MoneyDeposited;

/**
 * Application event handler for MoneyDeposited domain events.
 * @extends DomainEventHandler<MoneyDeposited>
 */
interface MoneyDepositedEventHandler extends DomainEventHandler
{
}
