<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\AccountOpened;

use Examples\BankAccount\Domain\Events\AccountOpened;
use SeedWork\Application\IntegrationEventPublisher;
use SeedWork\Domain\DomainEvent;

final readonly class AccountOpenedDomainEventHandler implements AccountOpenedEventHandler
{
    public function __construct(
        private IntegrationEventPublisher $publisher,
    ) {}

    /**
     * @param AccountOpened $event
     */
    public function handle(DomainEvent $event): void
    {
        assert($event instanceof AccountOpened);

        $this->publisher->publish(
            new AccountOpenedIntegrationEvent(
                accountId: (string) $event->accountId,
                currency: $event->initialBalance->currency->value,
                correlationId: $event->id
            )
        );
    }
}
