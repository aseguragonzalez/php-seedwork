<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\TestValueObject;

/**
 * @internal
 *
 * @coversNothing
 */
final class ValueObjectTest extends TestCase
{
    public function testValidationFailureThrowsDomainException(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Value cannot be empty.');

        new TestValueObject('');
    }
}
