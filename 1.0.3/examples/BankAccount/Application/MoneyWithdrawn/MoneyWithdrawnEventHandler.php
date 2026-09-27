<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\MoneyWithdrawn;

use Examples\BankAccount\Domain\Events\MoneyWithdrawn;
use SeedWork\Application\DomainEventHandler;

/**
 * Application event handler for MoneyWithdrawn domain events.
 *
 * @extends DomainEventHandler<MoneyWithdrawn>
 */
interface MoneyWithdrawnEventHandler extends DomainEventHandler {}
