<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\MoneyTransferredIn;

use Seedwork\Domain\DomainEvent;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyTransferredIn;

/**
 * Default event handler for the MoneyTransferredIn event.
 */
final readonly class DefaultMoneyTransferredInEventHandler implements MoneyTransferredInEventHandler
{
    /**
     * @param MoneyTransferredIn $event
     */
    public function execute(DomainEvent $event): void
    {
        // Example: react to incoming transfer (e.g. update projections)
        // $event->toAccountId->value;
        // $event->fromAccountId->value;
        // $event->amount->amount;
        // $event->transactionId->value;
    }
}
