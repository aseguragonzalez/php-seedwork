<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandBus;
use SeedWork\Application\Result;
use SeedWork\Application\ValidationErrors;
use SeedWork\Infrastructure\ValidationCommandBus;
use Tests\Fixtures\TestCommand;

final class ValidationCommandBusTest extends TestCase
{
    public function testDispatchDelegatesToInnerBusWhenValidationPasses(): void
    {
        $command = new TestCommand('valid-payload');
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn(Result::ok());

        $bus = new ValidationCommandBus($innerBus);
        $result = $bus->dispatch($command);

        $this->assertTrue($result->isOk());
    }

    public function testDispatchThrowsAndSkipsInnerBusWhenValidationFails(): void
    {
        $command = new TestCommand('');
        $innerBus = $this->createMock(CommandBus::class);
        $innerBus->expects($this->never())->method('dispatch');

        $bus = new ValidationCommandBus($innerBus);

        $this->expectException(ValidationErrors::class);
        $bus->dispatch($command);
    }
}
