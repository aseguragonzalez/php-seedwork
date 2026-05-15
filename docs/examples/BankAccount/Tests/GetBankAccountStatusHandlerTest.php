<?php

declare(strict_types=1);

namespace Examples\BankAccount\Tests;

use PHPUnit\Framework\TestCase;
use SeedWork\Infrastructure\QueryBusBuilder;
use SeedWork\Infrastructure\RegistryQueryBus;
use Examples\BankAccount\Application\GetBankAccountStatus\BankAccountStatusResult;
use Examples\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQuery;
use Examples\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQueryHandler;
use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Infrastructure\Repositories\InMemoryBankAccountRepository;

final class GetBankAccountStatusHandlerTest extends TestCase
{
    public function testExistingAccountReturnsMaybeWithResult(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $account = BankAccount::create(initialBalance: new AccountBalance(200, Currency::EUR));
        $repository->save($account);

        $registry = new RegistryQueryBus();
        $registry->register(
            GetBankAccountStatusQuery::class,
            new GetBankAccountStatusQueryHandler($repository)
        );
        $bus = (new QueryBusBuilder($registry))->withValidation()->build();

        $maybe = $bus->ask(new GetBankAccountStatusQuery($account->id->value));

        $this->assertTrue($maybe->hasValue());
        /** @var BankAccountStatusResult $result */
        $result = $maybe->value();
        $this->assertSame(200, $result->balance->amount);
        $this->assertSame(Currency::EUR, $result->balance->currency);
    }

    public function testNonExistingAccountReturnsMaybeNothing(): void
    {
        $repository = new InMemoryBankAccountRepository();

        $registry = new RegistryQueryBus();
        $registry->register(
            GetBankAccountStatusQuery::class,
            new GetBankAccountStatusQueryHandler($repository)
        );
        $bus = (new QueryBusBuilder($registry))->withValidation()->build();

        $maybe = $bus->ask(new GetBankAccountStatusQuery('acc-nonexistent.0'));

        $this->assertFalse($maybe->hasValue());
    }
}
