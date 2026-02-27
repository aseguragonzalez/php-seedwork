<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Application\DepositMoney;

use SeedWork\Application\Command;
use SeedWork\Application\DomainEventBus;
use Tests\Fixtures\BankAccount\Domain\BankAccountObtainer;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

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
        $accountId = BankAccountId::fromString($command->accountId);
        $amount = new Money($command->amount, Currency::from($command->currency));

        $account = $this->obtainer->obtain($accountId)->deposit($amount);

        $this->repository->save($account);

        $this->domainEventBus->publish($account->collectEvents());
    }
}
