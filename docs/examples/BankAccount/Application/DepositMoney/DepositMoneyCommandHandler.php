<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\DepositMoney;

use SeedWork\Application\Command;
use SeedWork\Domain\Repository;
use Examples\BankAccount\Domain\BankAccountObtainer;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Domain\ValueObjects\Money;

/**
 * Handler for the DepositMoney command.
 */
final readonly class DepositMoneyCommandHandler implements DepositMoney
{
    public function __construct(
        private BankAccountObtainer $obtainer,
        private Repository $repository,
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

        $account = $this->obtainer->obtain($accountId)->deposit($amount);

        $this->repository->save($account);
    }
}
