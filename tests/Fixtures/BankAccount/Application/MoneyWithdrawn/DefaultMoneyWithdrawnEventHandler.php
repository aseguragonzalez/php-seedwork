<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\MoneyWithdrawn;

use Seedwork\Domain\DomainEvent;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyWithdrawn;

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
