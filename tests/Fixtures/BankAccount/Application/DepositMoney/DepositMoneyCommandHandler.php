<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\DepositMoney;

use SeedWork\Application\Command;
use SeedWork\Application\DomainEventBus;
use Tests\Fixtures\BankAccount\Domain\BankAccountObtainer;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;

/**
 * Handler for the DepositMoney command.
 */
final readonly class DepositMoneyCommandHandler implements DepositMoney
{
    public function __construct(
        private BankAccountObtainer $obtainer,
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
        $account = $this->obtainer->obtain($command->accountId)->deposit($command->amount);

        $this->repository->save($account);

        $this->domainEventBus->publish($account->collectEvents());
    }
}
