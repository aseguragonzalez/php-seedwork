<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\MoneyWithdrawn;

use Examples\BankAccount\Domain\Events\MoneyWithdrawn;
use SeedWork\Domain\DomainEvent;

/**
 * Default event handler for the MoneyWithdrawn event.
 */
final readonly class DefaultMoneyWithdrawnEventHandler implements MoneyWithdrawnEventHandler
{
    /**
     * @param MoneyWithdrawn $event
     */
    public function handle(DomainEvent $event): void
    {
        // Example: react to a withdrawal (e.g. audit log, analytics)
        // $event->accountId->value;
        // $event->amount->amount;
        // $event->transactionId->value;
    }
}
