<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\TransferMoney;

use Seedwork\Application\Command;
use Seedwork\Application\DomainEventBus;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;

/**
 * Handler for the TransferMoney command.
 */
final readonly class TransferMoneyCommandHandler implements TransferMoney
{
    public function __construct(
        private BankAccountRepository $repository,
        private DomainEventBus $domainEventBus
    ) {
    }

    /**
     * @param TransferMoneyCommand $command
     */
    public function handle(Command $command): void
    {
        $fromAccount = $this->repository->findBy($command->fromAccountId);
        $toAccount = $this->repository->findBy($command->toAccountId);
        if ($fromAccount === null || $toAccount === null) {
            throw new \RuntimeException('BankAccount not found');
        }

        $fromAccount = $fromAccount->transferOut($command->amount, $command->toAccountId);
        $toAccount = $toAccount->transferIn($command->amount, $command->fromAccountId);

        $this->repository->save($fromAccount);
        $this->repository->save($toAccount);

        $this->domainEventBus->publish([...$fromAccount->collectEvents(), ...$toAccount->collectEvents()]);
    }
}
