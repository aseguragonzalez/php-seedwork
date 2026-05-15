<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use SeedWork\Domain\Exceptions\ValueException;
use Tests\Fixtures\TestEventId;

final class EventIdTest extends TestCase
{
    public function testEquals(): void
    {
        $id1 = TestEventId::create();
        $id2 = TestEventId::create();
        $id3 = TestEventId::fromString($id1->value);

        $this->assertFalse($id1->equals($id2));
        $this->assertTrue($id1->equals($id3));
    }

    public function testToString(): void
    {
        $id = TestEventId::create();

        $this->assertSame($id->value, (string) $id);
    }

    public function testValidationFailsWhenEmpty(): void
    {
        $this->expectException(ValueException::class);
        $this->expectExceptionMessage('TestEventId cannot be empty.');

        TestEventId::fromString('');
    }
}
