<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandHandler;
use SeedWork\Infrastructure\ContainerCommandBus;
use Examples\BankAccount\Application\DepositMoney\DepositMoneyCommand;
use Examples\BankAccount\Application\WithdrawMoney\WithdrawMoneyCommand;
use Examples\BankAccount\Domain\Entities\BankAccountId;
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
        $result = $bus->dispatch($command);

        $this->assertTrue($result->isOk());
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
            'No handler registered for command Examples\BankAccount\Application\DepositMoney\DepositMoneyCommand.'
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
        $expected = 'Handler for command type Examples\BankAccount\Application\DepositMoney'
            . '\DepositMoneyCommand is not a valid handler.';
        $this->expectExceptionMessage($expected);

        $bus->dispatch($command);
    }

    public function testDispatchReturnFailedResultWhenHandlerThrowsDomainException(): void
    {
        $command = $this->createDepositMoneyCommand();
        $handler = $this->createMock(CommandHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException(new \SeedWork\Domain\Exceptions\ValueException('Insufficient funds', 422));
        $container = new FakeContainer(['depositHandler' => $handler]);
        $bus = new ContainerCommandBus($container);
        $bus->register(DepositMoneyCommand::class, 'depositHandler');

        $result = $bus->dispatch($command);

        $this->assertTrue($result->isFail());
        $errors = $result->errors();
        $this->assertCount(1, $errors);
        $this->assertSame('422', $errors[0]->code);
        $this->assertSame('Insufficient funds', $errors[0]->message);
    }

    private function createDepositMoneyCommand(): DepositMoneyCommand
    {
        return new DepositMoneyCommand(
            BankAccountId::create()->value,
            100,
            'USD'
        );
    }

    private function createWithdrawMoneyCommand(): WithdrawMoneyCommand
    {
        return new WithdrawMoneyCommand(
            BankAccountId::create()->value,
            50,
            'USD'
        );
    }
}
