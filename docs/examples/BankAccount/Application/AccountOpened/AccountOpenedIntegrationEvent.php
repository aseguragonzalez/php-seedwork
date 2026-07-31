<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\AccountOpened;

use SeedWork\Application\IntegrationEvent;

/**
 * Notifies other bounded contexts that a new bank account was opened.
 *
 * Specializes {@see IntegrationEvent}'s payload template so consumers get a typed
 * shape instead of `array<string, mixed>` when reading `$payload`.
 *
 * @extends IntegrationEvent<array{accountId: string, currency: string}>
 */
final readonly class AccountOpenedIntegrationEvent extends IntegrationEvent
{
    public function __construct(string $accountId, string $currency, string $correlationId)
    {
        parent::__construct(
            type: 'bank_account.account_opened',
            version: '1.0',
            aggregateId: $accountId,
            payload: [
                'accountId' => $accountId,
                'currency' => $currency,
            ],
            correlationId: $correlationId
        );
    }
}
