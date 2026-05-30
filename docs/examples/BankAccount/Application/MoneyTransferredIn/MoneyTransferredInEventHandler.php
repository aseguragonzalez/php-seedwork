<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\MoneyTransferredIn;

use Examples\BankAccount\Domain\Events\MoneyTransferredIn;
use SeedWork\Application\DomainEventHandler;

/**
 * Application event handler for MoneyTransferredIn domain events.
 *
 * @extends DomainEventHandler<MoneyTransferredIn>
 */
interface MoneyTransferredInEventHandler extends DomainEventHandler {}
