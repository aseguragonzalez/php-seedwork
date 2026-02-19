<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\MoneyDeposited;

use Seedwork\Application\DomainEventHandler;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyDeposited;

/**
 * Application event handler for MoneyDeposited domain events.
 * @extends DomainEventHandler<MoneyDeposited>
 */
interface MoneyDepositedEventHandler extends DomainEventHandler
{
}
