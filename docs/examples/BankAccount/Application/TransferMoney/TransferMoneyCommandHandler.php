<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\TransferMoney;

use SeedWork\Application\Command;
use SeedWork\Application\DomainEventBus;
use Examples\BankAccount\Domain\BankAccountObtainer;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Repositories\BankAccountRepository;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Domain\ValueObjects\Money;

/**
 * Handler for the TransferMoney command.
 */
final readonly class TransferMoneyCommandHandler implements TransferMoney
{
    public function __construct(
        private BankAccountObtainer $obtainer,
        private BankAccountRepository $repository,
        private DomainEventBus $domainEventBus
    ) {
    }

    /**
     * @param TransferMoneyCommand $command
     */
    public function handle(Command $command): void
    {
        $fromAccountId = BankAccountId::fromString($command->fromAccountId);
        $toAccountId = BankAccountId::fromString($command->toAccountId);
        $amount = new Money($command->amount, Currency::from($command->currency));

        $fromAccount = $this->obtainer
            ->obtain($fromAccountId)
            ->transferOut($amount, $toAccountId);
        $toAccount = $this->obtainer
            ->obtain($toAccountId)
            ->transferIn($amount, $fromAccountId);

        $this->repository->save($fromAccount);
        $this->repository->save($toAccount);

        $this->domainEventBus->publish([...$fromAccount->collectEvents(), ...$toAccount->collectEvents()]);
    }
}
