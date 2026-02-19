<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\MoneyTransferredOut;

use Seedwork\Domain\DomainEvent;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyTransferredOut;

/**
 * Default event handler for the MoneyTransferredOut event.
 */
final readonly class DefaultMoneyTransferredOutEventHandler implements MoneyTransferredOutEventHandler
{
    /**
     * @param MoneyTransferredOut $event
     */
    public function execute(DomainEvent $event): void
    {
        // Example: react to outgoing transfer (e.g. reconciliation)
        // $event->amount->amount;
        // $event->transactionId->value;
        // $event->toAccountId->value;
    }
}
