<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\GetBankAccountStatus;

use SeedWork\Application\QueryResult;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;

/**
 * @param array<TransactionDto> $transactions
 */
final readonly class BankAccountStatusResult extends QueryResult
{
    /**
     * @param BankAccountId $accountId
     * @param AccountBalance $balance
     * @param array<TransactionDto> $transactions
     */
    public function __construct(
        public BankAccountId $accountId,
        public AccountBalance $balance,
        public array $transactions
    ) {
        parent::__construct();
    }
}
