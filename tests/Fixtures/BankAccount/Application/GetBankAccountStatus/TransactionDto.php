<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\GetBankAccountStatus;

use Tests\Fixtures\BankAccount\Domain\ValueObjects\TransactionType;

final readonly class TransactionDto
{
    public function __construct(
        public string $id,
        public TransactionType $type,
        public int $amount,
        public string $currency,
        public string $createdAt,
    ) {
    }
}
