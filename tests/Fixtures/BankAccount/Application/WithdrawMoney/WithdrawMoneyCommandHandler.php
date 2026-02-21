<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\WithdrawMoney;

use Seedwork\Application\Command;
use Seedwork\Application\DomainEventBus;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;

/**
 * Handler for the WithdrawMoney command.
 */
final readonly class WithdrawMoneyCommandHandler implements WithdrawMoney
{
    public function __construct(
        private BankAccountRepository $repository,
        private DomainEventBus $domainEventBus
    ) {
    }

    /**
     * @param WithdrawMoneyCommand $command
     */
    public function handle(Command $command): void
    {
        $account = $this->repository->findBy($command->accountId);
        if ($account === null) {
            throw new \RuntimeException('BankAccount not found');
        }
        $account = $account->withdraw($command->amount);

        $this->repository->save($account);

        $this->domainEventBus->publish($account->collectEvents());
    }
}
