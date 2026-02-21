<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\MoneyWithdrawn;

use SeedWork\Application\DomainEventHandler;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyWithdrawn;

/**
 * Application event handler for MoneyWithdrawn domain events.
 * @extends DomainEventHandler<MoneyWithdrawn>
 */
interface MoneyWithdrawnEventHandler extends DomainEventHandler
{
}
