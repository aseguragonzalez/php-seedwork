<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use SeedWork\Domain\Exceptions\ValueException;
use Tests\Fixtures\TestId;

final class EntityIdTest extends TestCase
{
    public function testEquals(): void
    {
        $id1 = TestId::create();
        $id2 = TestId::create();
        $id3 = TestId::fromString($id1->value);

        $this->assertFalse($id1->equals($id2));
        $this->assertTrue($id1->equals($id3));
    }

    public function testToString(): void
    {
        $id = TestId::create();

        $this->assertSame($id->value, (string) $id);
    }

    public function testValidationFailsWhenEmpty(): void
    {
        $this->expectException(ValueException::class);
        $this->expectExceptionMessage('TestId cannot be empty.');

        TestId::fromString('');
    }
}
