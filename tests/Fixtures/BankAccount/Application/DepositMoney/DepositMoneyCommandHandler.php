<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\DepositMoney;

use Seedwork\Application\Command;
use Seedwork\Application\DomainEventBus;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;

/**
 * Handler for the DepositMoney command.
 */
final readonly class DepositMoneyCommandHandler implements DepositMoney
{
    public function __construct(
        private BankAccountRepository $repository,
        private DomainEventBus $domainEventBus
    ) {
    }

    /**
     * @param DepositMoneyCommand $command
     * @return void
     */
    public function handle(Command $command): void
    {
        $account = $this->repository->findBy($command->accountId);
        if ($account === null) {
            throw new \RuntimeException('BankAccount not found');
        }
        $account = $account->deposit($command->amount);

        $this->repository->save($account);

        $this->domainEventBus->publish($account->collectEvents());
    }
}
