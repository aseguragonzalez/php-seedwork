<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\MoneyDeposited;

use SeedWork\Domain\DomainEvent;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyDeposited;

/**
 * Default event handler for the MoneyDeposited event.
 */
final readonly class DefaultMoneyDepositedEventHandler implements MoneyDepositedEventHandler
{
    /**
     * @param MoneyDeposited $event
     * @return void
     */
    public function handle(DomainEvent $event): void
    {
        // Example: react to a deposit (e.g. send notification, update read model)
        // $event->accountId->value;
        // $event->amount->amount;
        // $event->transactionId->value;
    }
}
