<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandBus;
use SeedWork\Application\CommandValidator;
use SeedWork\Application\ValidationError;
use SeedWork\Application\ValidationErrors;
use SeedWork\Infrastructure\ValidationCommandBus;
use Tests\Fixtures\BankAccount\Application\DepositMoney\DepositMoneyCommand;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;

final class ValidationCommandBusTest extends TestCase
{
    public function testDispatchValidatesCommandBeforeDelegating(): void
    {
        $command = $this->createDepositMoneyCommand();
        $validator = $this->createMock(CommandValidator::class);
        $validator->expects($this->once())->method('validate')->with($command);
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())->method('dispatch')->with($command);

        $bus = new ValidationCommandBus($innerBus, $validator);
        $bus->dispatch($command);
    }

    public function testDispatchThrowsAndSkipsInnerBusWhenValidationFails(): void
    {
        $command = $this->createDepositMoneyCommand();
        $errors = new ValidationErrors([new ValidationError('amount', 'must be positive')]);
        $validator = $this->createMock(CommandValidator::class);
        $validator->expects($this->once())->method('validate')->willThrowException($errors);
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->never())->method('dispatch');

        $bus = new ValidationCommandBus($innerBus, $validator);

        $this->expectException(ValidationErrors::class);
        $bus->dispatch($command);
    }

    private function createDepositMoneyCommand(): DepositMoneyCommand
    {
        return new DepositMoneyCommand(BankAccountId::create()->value, 100, 'USD');
    }
}
