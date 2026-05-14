<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\Command;
use SeedWork\Application\CommandBus;
use SeedWork\Application\Result;
use SeedWork\Application\ResultError;
use SeedWork\Application\ValidationErrors;
use SeedWork\Infrastructure\ValidationCommandBus;
use Examples\BankAccount\Application\DepositMoney\DepositMoneyCommand;
use Examples\BankAccount\Domain\Entities\BankAccountId;

final class ValidationCommandBusTest extends TestCase
{
    public function testDispatchDelegatesToInnerBusWhenValidationPasses(): void
    {
        $command = $this->createValidDepositMoneyCommand();
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn(Result::ok());

        $bus = new ValidationCommandBus($innerBus);
        $result = $bus->dispatch($command);

        $this->assertTrue($result->isOk());
    }

    public function testDispatchReturnsFailedResultWhenValidationFails(): void
    {
        $command = $this->createInvalidDepositMoneyCommand();
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->never())->method('dispatch');

        $bus = new ValidationCommandBus($innerBus);
        $result = $bus->dispatch($command);

        $this->assertTrue($result->isFail());
        $this->assertNotEmpty($result->errors());
    }

    public function testDispatchFailedResultContainsValidationErrors(): void
    {
        $command = $this->createInvalidDepositMoneyCommand();
        $innerBus = $this->createMock(CommandBus::class);
        $bus = new ValidationCommandBus($innerBus);

        $result = $bus->dispatch($command);

        $this->assertInstanceOf(ResultError::class, $result->errors()[0]);
        $this->assertSame('amount', $result->errors()[0]->code);
    }

    public function testDispatchWithEmptyValidationErrorsReturnsFallbackError(): void
    {
        $command = new readonly class extends Command {
            public function __construct()
            {
                parent::__construct();
            }

            public function validate(): void
            {
                throw new ValidationErrors([]);
            }
        };
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->never())->method('dispatch');

        $bus = new ValidationCommandBus($innerBus);
        $result = $bus->dispatch($command);

        $this->assertTrue($result->isFail());
        $this->assertCount(1, $result->errors());
        $this->assertSame('validation', $result->errors()[0]->code);
        $this->assertSame('Validation failed', $result->errors()[0]->message);
    }

    private function createValidDepositMoneyCommand(): DepositMoneyCommand
    {
        return new DepositMoneyCommand(BankAccountId::create()->value, 100, 'USD');
    }

    private function createInvalidDepositMoneyCommand(): DepositMoneyCommand
    {
        return new DepositMoneyCommand(BankAccountId::create()->value, -1, 'USD');
    }
}
