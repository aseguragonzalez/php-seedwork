<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\MoneyTransferredIn;

use SeedWork\Application\DomainEventHandler;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyTransferredIn;

/**
 * Application event handler for MoneyTransferredIn domain events.
 * @extends DomainEventHandler<MoneyTransferredIn>
 */
interface MoneyTransferredInEventHandler extends DomainEventHandler
{
}
