<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Entities;

use Seedwork\Domain\Entity;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\TransactionType;

/**
 * @extends Entity<TransactionId>
 */
final readonly class Transaction extends Entity
{
    public function __construct(
        TransactionId $id,
        public TransactionType $type,
        public Money $amount,
        public \DateTimeImmutable $createdAt,
        public ?BankAccountId $relatedAccountId = null
    ) {
        parent::__construct($id);
    }
}
