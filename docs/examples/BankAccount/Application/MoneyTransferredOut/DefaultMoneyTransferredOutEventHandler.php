<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\MoneyTransferredOut;

use SeedWork\Domain\DomainEvent;
use Examples\BankAccount\Domain\Events\MoneyTransferredOut;

/**
 * Default event handler for the MoneyTransferredOut event.
 */
final readonly class DefaultMoneyTransferredOutEventHandler implements MoneyTransferredOutEventHandler
{
    /**
     * @param MoneyTransferredOut $event
     */
    public function handle(DomainEvent $event): void
    {
        // Example: react to outgoing transfer (e.g. reconciliation)
        // $event->amount->amount;
        // $event->transactionId->value;
        // $event->toAccountId->value;
    }
}
