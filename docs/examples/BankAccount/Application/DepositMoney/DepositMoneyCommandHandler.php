<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\DepositMoney;

use SeedWork\Application\Command;
use Examples\BankAccount\Domain\Exceptions\BankAccountException;
use Examples\BankAccount\Domain\Repositories\BankAccountRepository;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Domain\ValueObjects\Money;

final readonly class DepositMoneyCommandHandler implements DepositMoney
{
    public function __construct(
        private BankAccountRepository $repository,
    ) {
    }

    /**
     * @param DepositMoneyCommand $command
     * @return void
     */
    public function handle(Command $command): void
    {
        $accountId = BankAccountId::fromString($command->accountId);
        $amount = new Money($command->amount, Currency::from($command->currency));

        $account = $this->repository->findById($accountId)
            ?? throw new BankAccountException("BankAccount '{$accountId->value}' not found");

        $this->repository->save($account->deposit($amount));
    }
}
