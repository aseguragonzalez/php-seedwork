<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Seedwork\Domain\Exceptions\ValueException;
use Tests\Fixtures\BankAccount\Domain\Events\BankAccountEventId;

final class EventIdTest extends TestCase
{
    public function testEquals(): void
    {
        $id1 = BankAccountEventId::create();
        $id2 = BankAccountEventId::create();
        $id3 = BankAccountEventId::fromString($id1->value);

        $this->assertFalse($id1->equals($id2));
        $this->assertTrue($id1->equals($id3));
    }

    public function testToString(): void
    {
        $id = BankAccountEventId::create();
        $this->assertSame($id->value, (string)$id);
    }

    public function testValidationFailsWhenEmpty(): void
    {
        $this->expectException(ValueException::class);
        $this->expectExceptionMessage('Event id cannot be empty');

        BankAccountEventId::fromString('');
    }

    public function testValidationFailsWhenDoesNotStartWithEv(): void
    {
        $this->expectException(ValueException::class);
        $this->expectExceptionMessage('Event id must start with "evt-"');

        BankAccountEventId::fromString('1234567890');
    }
}
