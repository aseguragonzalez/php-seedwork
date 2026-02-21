<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\GetBankAccountStatus;

use Seedwork\Application\Query;
use Seedwork\Application\QueryResult;
use Tests\Fixtures\BankAccount\Domain\BankAccountObtainer;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Entities\Transaction;

/**
 * Handler for the GetBankAccountStatus query.
 */
final readonly class GetBankAccountStatusQueryHandler implements GetBankAccountStatus
{
    public function __construct(
        private BankAccountObtainer $obtainer
    ) {
    }

    /**
     * @param GetBankAccountStatusQuery $query
     * @return BankAccountStatusResult
     */
    public function handle(Query $query): QueryResult
    {
        /** @var BankAccount $account */
        $account = $this->obtainer->obtain($query->accountId);

        $transactions = array_map(
            fn (Transaction $t) => new TransactionDto(
                $t->id->value,
                $t->type,
                $t->amount->amount,
                $t->amount->currency->value,
                $t->createdAt->format(\DateTimeInterface::ATOM),
            ),
            $account->getTransactions()
        );

        return new BankAccountStatusResult(
            $account->id,
            $account->getBalance(),
            $transactions
        );
    }
}
