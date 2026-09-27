<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\GetBankAccountStatus;

use Examples\BankAccount\Domain\ValueObjects\TransactionType;

final readonly class TransactionDto
{
    public function __construct(
        public string $id,
        public TransactionType $type,
        public int $amount,
        public string $currency,
        public string $createdAt,
    ) {}
}
