<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandHandler;
use SeedWork\Infrastructure\ContainerCommandBus;
use Tests\Fixtures\BankAccount\Application\DepositMoney\DepositMoneyCommand;
use Tests\Fixtures\BankAccount\Application\WithdrawMoney\WithdrawMoneyCommand;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;
use Tests\Fixtures\FakeContainer;

final class ContainerCommandBusTest extends TestCase
{
    public function testDispatchInvokesRegisteredHandlerWithCommand(): void
    {
        $command = $this->createDepositMoneyCommand();
        $handler = $this->createMock(CommandHandler::class);
        $handler->expects($this->once())->method('handle')->with($command);
        $container = new FakeContainer(['depositHandler' => $handler]);
        $bus = new ContainerCommandBus($container);

        $bus->register(DepositMoneyCommand::class, 'depositHandler');
        $bus->dispatch($command);
    }

    public function testDispatchInvokesCorrectHandlerPerCommandType(): void
    {
        $depositCommand = $this->createDepositMoneyCommand();
        $withdrawCommand = $this->createWithdrawMoneyCommand();
        $handlerDeposit = $this->createMock(CommandHandler::class);
        $handlerDeposit->expects($this->once())->method('handle')->with($depositCommand);
        $handlerWithdraw = $this->createMock(CommandHandler::class);
        $handlerWithdraw->expects($this->once())->method('handle')->with($withdrawCommand);
        $container = new FakeContainer([
            'handlerDeposit' => $handlerDeposit,
            'handlerWithdraw' => $handlerWithdraw,
        ]);
        $bus = new ContainerCommandBus($container);
        $bus->register(DepositMoneyCommand::class, 'handlerDeposit');
        $bus->register(WithdrawMoneyCommand::class, 'handlerWithdraw');

        $bus->dispatch($depositCommand);
        $bus->dispatch($withdrawCommand);
    }

    public function testDispatchThrowsWhenNoHandlerRegistered(): void
    {
        $command = $this->createDepositMoneyCommand();
        $container = new FakeContainer([]);
        $bus = new ContainerCommandBus($container);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'No handler registered for command Tests\Fixtures\BankAccount\Application\DepositMoney\DepositMoneyCommand.'
        );

        $bus->dispatch($command);
    }

    public function testDispatchThrowsWhenContainerReturnsNonHandler(): void
    {
        $command = $this->createDepositMoneyCommand();
        $container = new FakeContainer(['depositHandler' => new \stdClass()]);
        $bus = new ContainerCommandBus($container);
        $bus->register(DepositMoneyCommand::class, 'depositHandler');
        $this->expectException(\InvalidArgumentException::class);
        $expected = 'Handler for command type Tests\Fixtures\BankAccount\Application\DepositMoney'
            . '\DepositMoneyCommand is not a valid handler.';
        $this->expectExceptionMessage($expected);

        $bus->dispatch($command);
    }

    private function createDepositMoneyCommand(): DepositMoneyCommand
    {
        return new DepositMoneyCommand(
            BankAccountId::create(),
            new Money(100, Currency::USD)
        );
    }

    private function createWithdrawMoneyCommand(): WithdrawMoneyCommand
    {
        return new WithdrawMoneyCommand(
            BankAccountId::create(),
            new Money(50, Currency::USD)
        );
    }
}
