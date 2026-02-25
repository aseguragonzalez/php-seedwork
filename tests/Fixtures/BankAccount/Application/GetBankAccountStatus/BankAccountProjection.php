<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\GetBankAccountStatus;

/**
 * Read-model projection for a bank account (primitives only).
 *
 * @param array<TransactionProjection> $transactions
 */
final readonly class BankAccountProjection
{
    /**
     * @param array<TransactionProjection> $transactions
     */
    public function __construct(
        public string $id,
        public int $balanceAmount,
        public string $currency,
        public array $transactions,
    ) {
    }
}
