<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\TransferMoney;

use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Exceptions\BankAccountException;
use Examples\BankAccount\Domain\Repositories\BankAccountRepository;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Domain\ValueObjects\Money;
use SeedWork\Application\Command;

final readonly class TransferMoneyCommandHandler implements TransferMoney
{
    public function __construct(
        private BankAccountRepository $repository,
    ) {}

    /**
     * @param TransferMoneyCommand $command
     */
    public function handle(Command $command): void
    {
        $fromAccountId = BankAccountId::fromString($command->fromAccountId);
        $toAccountId = BankAccountId::fromString($command->toAccountId);
        $amount = new Money($command->amount, Currency::from($command->currency));

        $fromAccount = $this->repository->findById($fromAccountId)
            ?? throw new BankAccountException("BankAccount '{$fromAccountId->value}' not found");
        $toAccount = $this->repository->findById($toAccountId)
            ?? throw new BankAccountException("BankAccount '{$toAccountId->value}' not found");

        $this->repository->save($fromAccount->transferOut($amount, $toAccountId));
        $this->repository->save($toAccount->transferIn($amount, $fromAccountId));
    }
}
