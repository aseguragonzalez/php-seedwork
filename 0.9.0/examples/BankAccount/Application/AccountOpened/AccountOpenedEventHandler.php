<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\AccountOpened;

use Examples\BankAccount\Domain\Events\AccountOpened;
use SeedWork\Application\DomainEventHandler;

/**
 * Application event handler for AccountOpened domain events.
 *
 * @extends DomainEventHandler<AccountOpened>
 */
interface AccountOpenedEventHandler extends DomainEventHandler {}
