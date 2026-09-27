<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\WithdrawMoney;

use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Exceptions\BankAccountException;
use Examples\BankAccount\Domain\Repositories\BankAccountRepository;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Domain\ValueObjects\Money;
use SeedWork\Application\Command;

final readonly class WithdrawMoneyCommandHandler implements WithdrawMoney
{
    public function __construct(
        private BankAccountRepository $repository,
    ) {}

    /**
     * @param WithdrawMoneyCommand $command
     */
    public function handle(Command $command): void
    {
        $accountId = BankAccountId::fromString($command->accountId);
        $amount = new Money($command->amount, Currency::from($command->currency));

        $account = $this->repository->findById($accountId)
            ?? throw new BankAccountException("BankAccount '{$accountId->value}' not found");

        $this->repository->save($account->withdraw($amount));
    }
}
