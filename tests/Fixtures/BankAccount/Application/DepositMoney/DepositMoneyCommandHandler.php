<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\DepositMoney;

use Seedwork\Application\Command;
use Seedwork\Application\DomainEventsBus;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;

/**
 * Handler for the DepositMoney command.
 */
final readonly class DepositMoneyCommandHandler implements DepositMoney
{
    public function __construct(
        private BankAccountRepository $repository,
        private DomainEventsBus $domainEventsBus
    ) {
    }

    /**
     * @param DepositMoneyCommand $command
     * @return void
     */
    public function handle(Command $command): void
    {
        $account = $this->repository->getById($command->accountId);
        $account = $account->deposit($command->amount);

        $this->repository->save($account);

        foreach ($account->collectEvents() as $event) {
            $this->domainEventsBus->publish($event);
        }
    }
}
