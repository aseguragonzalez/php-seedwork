<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\AccountOpened;

use SeedWork\Application\IntegrationEventPublisher;
use SeedWork\Domain\DomainEvent;
use Examples\BankAccount\Domain\Events\AccountOpened;

final readonly class AccountOpenedDomainEventHandler implements AccountOpenedEventHandler
{
    public function __construct(
        private IntegrationEventPublisher $publisher,
    ) {
    }

    /**
     * @param AccountOpened $event
     */
    public function handle(DomainEvent $event): void
    {
        // Example: publish an integration event to notify other bounded contexts
        // that a new account has been opened.
        //
        // assert($event instanceof AccountOpened);
        // $this->publisher->publish(
        //     new AccountOpenedIntegrationEvent(
        //         accountId: $event->accountId->value,
        //         currency:  $event->initialBalance->currency->value,
        //     )
        // );
    }
}
