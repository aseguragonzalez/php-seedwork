<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\TransferMoney;

use Seedwork\Application\Command;
use Seedwork\Application\DomainEventsBus;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;

/**
 * Handler for the TransferMoney command.
 */
final readonly class TransferMoneyCommandHandler implements TransferMoney
{
    public function __construct(
        private BankAccountRepository $repository,
        private DomainEventsBus $domainEventsBus
    ) {
    }

    /**
     * @param TransferMoneyCommand $command
     */
    public function handle(Command $command): void
    {
        $fromAccount = $this->repository->getById($command->fromAccountId);
        $toAccount = $this->repository->getById($command->toAccountId);

        $fromAccount = $fromAccount->transferOut($command->amount, $command->toAccountId);
        $toAccount = $toAccount->transferIn($command->amount, $command->fromAccountId);

        $this->repository->save($fromAccount);
        $this->repository->save($toAccount);

        foreach ($fromAccount->collectEvents() as $event) {
            $this->domainEventsBus->publish($event);
        }
        foreach ($toAccount->collectEvents() as $event) {
            $this->domainEventsBus->publish($event);
        }
    }
}
