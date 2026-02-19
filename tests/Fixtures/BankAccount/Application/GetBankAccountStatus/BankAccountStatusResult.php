<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\GetBankAccountStatus;

use Seedwork\Application\QueryResult;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\AccountBalance;

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
