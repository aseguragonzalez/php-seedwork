<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\MoneyTransferredOut;

use Seedwork\Application\DomainEventHandler;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyTransferredOut;

/**
 * Application event handler for MoneyTransferredOut domain events.
 * @extends DomainEventHandler<MoneyTransferredOut>
 */
interface MoneyTransferredOutEventHandler extends DomainEventHandler
{
}
