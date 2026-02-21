<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\TransferMoney;

use Seedwork\Application\Command;
use Seedwork\Application\DomainEventBus;
use Tests\Fixtures\BankAccount\Domain\BankAccountObtainer;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;

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
        $fromAccount = $this->obtainer
            ->obtain($command->fromAccountId)
            ->transferOut($command->amount, $command->toAccountId);
        $toAccount = $this->obtainer
            ->obtain($command->toAccountId)
            ->transferIn($command->amount, $command->fromAccountId);

        $this->repository->save($fromAccount);
        $this->repository->save($toAccount);

        $this->domainEventBus->publish([...$fromAccount->collectEvents(), ...$toAccount->collectEvents()]);
    }
}
