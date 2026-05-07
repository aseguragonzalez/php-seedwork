<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\QueryBus;
use SeedWork\Application\QueryResult;
use SeedWork\Application\QueryValidator;
use SeedWork\Application\ValidationError;
use SeedWork\Application\ValidationErrors;
use SeedWork\Infrastructure\ValidationQueryBus;
use Examples\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQuery;
use Examples\BankAccount\Domain\Entities\BankAccountId;

final class ValidationQueryBusTest extends TestCase
{
    public function testAskValidatesQueryBeforeDelegating(): void
    {
        $query = $this->createGetBankAccountStatusQuery();
        $result = $this->createStub(QueryResult::class);
        $validator = $this->createMock(QueryValidator::class);
        $validator->expects($this->once())->method('validate')->with($query);
        $innerBus = $this->createMock(QueryBus::class);
        $innerBus->expects($this->once())->method('ask')->with($query)->willReturn($result);

        $bus = new ValidationQueryBus($innerBus, $validator);
        $actual = $bus->ask($query);

        self::assertSame($result, $actual);
    }

    public function testAskThrowsAndSkipsInnerBusWhenValidationFails(): void
    {
        $query = $this->createGetBankAccountStatusQuery();
        $errors = new ValidationErrors([new ValidationError('id', 'must be a valid UUID')]);
        $validator = $this->createMock(QueryValidator::class);
        $validator->expects($this->once())->method('validate')->willThrowException($errors);
        $innerBus = $this->createMock(QueryBus::class);
        $innerBus->expects($this->never())->method('ask');

        $bus = new ValidationQueryBus($innerBus, $validator);

        $this->expectException(ValidationErrors::class);
        $bus->ask($query);
    }

    private function createGetBankAccountStatusQuery(): GetBankAccountStatusQuery
    {
        return new GetBankAccountStatusQuery(BankAccountId::create()->value);
    }
}
