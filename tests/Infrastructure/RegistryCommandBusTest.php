<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandHandler;
use SeedWork\Infrastructure\RegistryCommandBus;
use Tests\Fixtures\AnotherTestCommand;
use Tests\Fixtures\TestCommand;
use Tests\Fixtures\TestDomainException;

final class RegistryCommandBusTest extends TestCase
{
    public function testDispatchInvokesRegisteredHandlerAndReturnsOk(): void
    {
        $command = new TestCommand('deposit');
        $handler = $this->createMock(CommandHandler::class);
        $handler->expects($this->once())->method('handle')->with($command);

        $bus = new RegistryCommandBus();
        $bus->register(TestCommand::class, $handler);

        $result = $bus->dispatch($command);

        $this->assertTrue($result->isOk());
    }

    public function testDispatchReturnsDomainExceptionAsFailedResult(): void
    {
        $command = new AnotherTestCommand();
        $handler = $this->createMock(CommandHandler::class);
        $handler->expects($this->once())->method('handle')->willThrowException(
            new TestDomainException('Insufficient funds.')
        );

        $bus = new RegistryCommandBus();
        $bus->register(AnotherTestCommand::class, $handler);

        $result = $bus->dispatch($command);

        $this->assertTrue($result->isFailed());
        $this->assertNotEmpty($result->errors());
        $this->assertSame('test_domain_exception', $result->errors()[0]->code);
        $this->assertStringContainsString('Insufficient', $result->errors()[0]->description);
    }

    public function testDispatchThrowsLogicExceptionWhenNoHandlerRegistered(): void
    {
        $command = new TestCommand();
        $bus = new RegistryCommandBus();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No handler for');

        $bus->dispatch($command);
    }

    public function testDispatchUsesCorrectHandlerForMultipleRegistrations(): void
    {
        $commandA = new TestCommand('op-a');
        $commandB = new AnotherTestCommand();
        $handlerA = $this->createMock(CommandHandler::class);
        $handlerA->expects($this->once())->method('handle')->with($commandA);
        $handlerB = $this->createMock(CommandHandler::class);
        $handlerB->expects($this->once())->method('handle')->with($commandB);

        $bus = new RegistryCommandBus();
        $bus->register(TestCommand::class, $handlerA);
        $bus->register(AnotherTestCommand::class, $handlerB);

        $bus->dispatch($commandA);
        $bus->dispatch($commandB);
    }
}
