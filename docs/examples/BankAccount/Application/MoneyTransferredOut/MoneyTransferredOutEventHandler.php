<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\MoneyTransferredOut;

use SeedWork\Application\DomainEventHandler;
use Examples\BankAccount\Domain\Events\MoneyTransferredOut;

/**
 * Application event handler for MoneyTransferredOut domain events.
 * @extends DomainEventHandler<MoneyTransferredOut>
 */
interface MoneyTransferredOutEventHandler extends DomainEventHandler
{
}
