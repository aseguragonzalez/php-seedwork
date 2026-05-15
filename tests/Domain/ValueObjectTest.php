<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use SeedWork\Domain\Exceptions\ValueException;
use Tests\Fixtures\TestValueObject;

final class ValueObjectTest extends TestCase
{
    public function testValidationFailureThrowsValueException(): void
    {
        $this->expectException(ValueException::class);
        $this->expectExceptionMessage('Value cannot be empty.');

        new TestValueObject('');
    }
}
