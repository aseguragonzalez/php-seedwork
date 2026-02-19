<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\WithdrawMoney;

use Seedwork\Application\Command;
use Seedwork\Application\DomainEventsBus;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;

/**
 * Handler for the WithdrawMoney command.
 */
final readonly class WithdrawMoneyCommandHandler implements WithdrawMoney
{
    public function __construct(
        private BankAccountRepository $repository,
        private DomainEventsBus $domainEventsBus
    ) {
    }

    /**
     * @param WithdrawMoneyCommand $command
     */
    public function handle(Command $command): void
    {
        $account = $this->repository->getById($command->accountId);
        $account = $account->withdraw($command->amount);

        $this->repository->save($account);

        foreach ($account->collectEvents() as $event) {
            $this->domainEventsBus->publish($event);
        }
    }
}
