<?php

declare(strict_types=1);

namespace Tests\Application;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\Maybe;

/**
 * @internal
 *
 * @coversNothing
 */
final class MaybeTest extends TestCase
{
    public function testJustHasValue(): void
    {
        $maybe = Maybe::just('hello');

        $this->assertTrue($maybe->hasValue());
        $this->assertSame('hello', $maybe->value());
    }

    public function testNothingHasNoValue(): void
    {
        $maybe = Maybe::nothing();

        $this->assertFalse($maybe->hasValue());
    }

    public function testNothingValueThrows(): void
    {
        $maybe = Maybe::nothing();

        $this->expectException(\LogicException::class);
        $maybe->value();
    }

    public function testJustWithObject(): void
    {
        $obj = new \stdClass();
        $maybe = Maybe::just($obj);

        $this->assertTrue($maybe->hasValue());
        $this->assertSame($obj, $maybe->value());
    }

    public function testJustWithNullThrowsInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maybe::just() cannot contain null. Use Maybe::nothing() instead.');

        Maybe::just(null);
    }
}
