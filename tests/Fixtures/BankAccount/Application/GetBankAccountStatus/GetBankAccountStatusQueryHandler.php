<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\GetBankAccountStatus;

use SeedWork\Application\Query;
use SeedWork\Application\QueryResult;
use SeedWork\Domain\Exceptions\NotFoundResource;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;

/**
 * Handler for the GetBankAccountStatus query.
 */
final readonly class GetBankAccountStatusQueryHandler implements GetBankAccountStatus
{
    public function __construct(
        private BankAccountRepository $repository,
    ) {
    }

    /**
     * @param GetBankAccountStatusQuery $query
     * @return BankAccountStatusResult
     */
    public function handle(Query $query): QueryResult
    {
        $accountId = BankAccountId::fromString($query->accountId);
        /** @var BankAccount|null $account */
        $account = $this->repository->findBy($accountId);

        if ($account === null) {
            throw new NotFoundResource('BankAccount', $accountId);
        }

        $transactions = array_map(
            fn ($t) => new TransactionDto(
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
            $transactions,
        );
    }
}
