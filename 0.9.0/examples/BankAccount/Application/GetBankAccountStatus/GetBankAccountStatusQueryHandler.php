<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\GetBankAccountStatus;

use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Repositories\BankAccountRepository;
use SeedWork\Application\Maybe;
use SeedWork\Application\Query;

/**
 * Handler for the GetBankAccountStatus query.
 */
final readonly class GetBankAccountStatusQueryHandler implements GetBankAccountStatus
{
    public function __construct(
        private BankAccountRepository $repository,
    ) {}

    /**
     * @param GetBankAccountStatusQuery $query
     *
     * @return Maybe<BankAccountStatusResult>
     */
    public function handle(Query $query): Maybe
    {
        $accountId = BankAccountId::fromString($query->accountId);

        /** @var null|BankAccount $account */
        $account = $this->repository->findById($accountId);

        if (null === $account) {
            return Maybe::nothing();
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

        return Maybe::just(new BankAccountStatusResult(
            $account->id,
            $account->getBalance(),
            $transactions,
        ));
    }
}
