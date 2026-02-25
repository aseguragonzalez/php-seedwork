<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\GetBankAccountStatus;

/**
 * Read-model projection for a single transaction (primitives only).
 */
final readonly class TransactionProjection
{
    public function __construct(
        public string $id,
        public string $type,
        public int $amount,
        public string $currency,
        public string $createdAt,
    ) {
    }
}
