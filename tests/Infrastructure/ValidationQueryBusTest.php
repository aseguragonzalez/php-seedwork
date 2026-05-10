<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\Maybe;
use SeedWork\Application\QueryBus;
use SeedWork\Application\ValidationErrors;
use SeedWork\Infrastructure\ValidationQueryBus;
use Examples\BankAccount\Application\GetBankAccountStatus\BankAccountStatusResult;
use Examples\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQuery;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;

final class ValidationQueryBusTest extends TestCase
{
    public function testAskDelegatesToInnerBusWhenValidationPasses(): void
    {
        $query = $this->createValidQuery();
        $expectedMaybe = Maybe::just(new BankAccountStatusResult(BankAccountId::create(), AccountBalance::zero(), []));
        $innerBus = $this->createMock(QueryBus::class);
        $innerBus->expects($this->once())
            ->method('ask')
            ->with($query)
            ->willReturn($expectedMaybe);

        $bus = new ValidationQueryBus($innerBus);
        $actual = $bus->ask($query);

        self::assertSame($expectedMaybe, $actual);
    }

    public function testAskThrowsAndSkipsInnerBusWhenValidationFails(): void
    {
        $query = $this->createInvalidQuery();
        $innerBus = $this->createMock(QueryBus::class);
        $innerBus->expects($this->never())->method('ask');

        $bus = new ValidationQueryBus($innerBus);

        $this->expectException(ValidationErrors::class);
        $bus->ask($query);
    }

    private function createValidQuery(): GetBankAccountStatusQuery
    {
        return new GetBankAccountStatusQuery(BankAccountId::create()->value);
    }

    private function createInvalidQuery(): GetBankAccountStatusQuery
    {
        return new GetBankAccountStatusQuery('');
    }
}
