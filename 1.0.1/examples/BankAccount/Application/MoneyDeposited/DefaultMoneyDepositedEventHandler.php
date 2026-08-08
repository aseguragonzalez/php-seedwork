<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\MoneyDeposited;

use Examples\BankAccount\Domain\Events\MoneyDeposited;
use SeedWork\Domain\DomainEvent;

/**
 * Default event handler for the MoneyDeposited event.
 */
final readonly class DefaultMoneyDepositedEventHandler implements MoneyDepositedEventHandler
{
    /**
     * @param MoneyDeposited $event
     */
    public function handle(DomainEvent $event): void
    {
        // Example: react to a deposit (e.g. send notification, update read model)
        // $event->accountId->value;
        // $event->amount->amount;
        // $event->transactionId->value;
    }
}
