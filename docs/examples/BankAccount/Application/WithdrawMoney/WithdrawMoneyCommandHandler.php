<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\WithdrawMoney;

use SeedWork\Application\Command;
use Examples\BankAccount\Domain\BankAccountObtainer;
use Examples\BankAccount\Domain\Repositories\BankAccountRepository;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Domain\ValueObjects\Money;

/**
 * Handler for the WithdrawMoney command.
 */
final readonly class WithdrawMoneyCommandHandler implements WithdrawMoney
{
    public function __construct(
        private BankAccountObtainer $obtainer,
        private BankAccountRepository $repository,
    ) {
    }

    /**
     * @param WithdrawMoneyCommand $command
     */
    public function handle(Command $command): void
    {
        $accountId = BankAccountId::fromString($command->accountId);
        $amount = new Money($command->amount, Currency::from($command->currency));

        $account = $this->obtainer->obtain($accountId)->withdraw($amount);

        $this->repository->save($account);
    }
}
