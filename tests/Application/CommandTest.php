<?php

declare(strict_types=1);

namespace Tests\Application;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\ValidationErrors;
use Tests\Fixtures\AnotherTestCommand;
use Tests\Fixtures\TestCommand;

/**
 * @internal
 *
 * @coversNothing
 */
final class CommandTest extends TestCase
{
    public function testConstructionSucceedsWhenValidationPasses(): void
    {
        $command = new TestCommand('valid-payload');

        self::assertSame('valid-payload', $command->payload);
    }

    public function testConstructionThrowsWhenValidationFails(): void
    {
        $this->expectException(ValidationErrors::class);

        new TestCommand('');
    }

    public function testDefaultValidateIsNoOp(): void
    {
        $command = new AnotherTestCommand();

        self::assertInstanceOf(AnotherTestCommand::class, $command);
    }
}
