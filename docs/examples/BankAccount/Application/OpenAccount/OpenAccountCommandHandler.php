<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\OpenAccount;

use SeedWork\Application\Command;
use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\Repositories\BankAccountRepository;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;
use Examples\BankAccount\Domain\ValueObjects\Currency;

final readonly class OpenAccountCommandHandler implements OpenAccount
{
    public function __construct(
        private BankAccountRepository $repository,
    ) {
    }

    /**
     * @param OpenAccountCommand $command
     */
    public function handle(Command $command): void
    {
        $currency = Currency::from($command->currency);
        $account = BankAccount::create(initialBalance: AccountBalance::zero($currency));

        $this->repository->save($account);
    }
}
