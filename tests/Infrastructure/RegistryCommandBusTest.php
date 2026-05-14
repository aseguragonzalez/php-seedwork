<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandHandler;
use SeedWork\Infrastructure\RegistryCommandBus;
use Examples\BankAccount\Application\DepositMoney\DepositMoneyCommand;
use Examples\BankAccount\Application\WithdrawMoney\WithdrawMoneyCommand;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\Exceptions\InsufficientFundsException;

final class RegistryCommandBusTest extends TestCase
{
    public function testDispatchInvokesRegisteredHandlerAndReturnsOk(): void
    {
        $command = $this->createDepositMoneyCommand();
        $handler = $this->createMock(CommandHandler::class);
        $handler->expects($this->once())->method('handle')->with($command);

        $bus = new RegistryCommandBus();
        $bus->register(DepositMoneyCommand::class, $handler);

        $result = $bus->dispatch($command);

        $this->assertTrue($result->isOk());
    }

    public function testDispatchReturnsDomainExceptionAsFailedResult(): void
    {
        $command = $this->createWithdrawMoneyCommand();
        $handler = $this->createMock(CommandHandler::class);
        $handler->expects($this->once())->method('handle')->willThrowException(
            InsufficientFundsException::forWithdrawal(10, 100)
        );

        $bus = new RegistryCommandBus();
        $bus->register(WithdrawMoneyCommand::class, $handler);

        $result = $bus->dispatch($command);

        $this->assertTrue($result->isFail());
        $this->assertNotEmpty($result->errors());
        $this->assertStringContainsString('Insufficient', $result->errors()[0]->message);
    }

    public function testDispatchThrowsLogicExceptionWhenNoHandlerRegistered(): void
    {
        $command = $this->createDepositMoneyCommand();
        $bus = new RegistryCommandBus();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No handler for');

        $bus->dispatch($command);
    }

    public function testDispatchUsesCorrectHandlerForMultipleRegistrations(): void
    {
        $depositCommand = $this->createDepositMoneyCommand();
        $withdrawCommand = $this->createWithdrawMoneyCommand();
        $depositHandler = $this->createMock(CommandHandler::class);
        $depositHandler->expects($this->once())->method('handle')->with($depositCommand);
        $withdrawHandler = $this->createMock(CommandHandler::class);
        $withdrawHandler->expects($this->once())->method('handle')->with($withdrawCommand);

        $bus = new RegistryCommandBus();
        $bus->register(DepositMoneyCommand::class, $depositHandler);
        $bus->register(WithdrawMoneyCommand::class, $withdrawHandler);

        $bus->dispatch($depositCommand);
        $bus->dispatch($withdrawCommand);
    }

    private function createDepositMoneyCommand(): DepositMoneyCommand
    {
        return new DepositMoneyCommand(BankAccountId::create()->value, 100, 'USD');
    }

    private function createWithdrawMoneyCommand(): WithdrawMoneyCommand
    {
        return new WithdrawMoneyCommand(BankAccountId::create()->value, 50, 'USD');
    }
}
