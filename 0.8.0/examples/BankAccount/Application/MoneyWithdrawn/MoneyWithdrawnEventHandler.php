<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\MoneyWithdrawn;

use SeedWork\Application\DomainEventHandler;
use Examples\BankAccount\Domain\Events\MoneyWithdrawn;

/**
 * Application event handler for MoneyWithdrawn domain events.
 * @extends DomainEventHandler<MoneyWithdrawn>
 */
interface MoneyWithdrawnEventHandler extends DomainEventHandler
{
}
