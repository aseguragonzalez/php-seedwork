<?php

declare(strict_types=1);

namespace Tests\Domain\Exceptions;

use PHPUnit\Framework\TestCase;
use Seedwork\Domain\Exceptions\DomainException;
use Seedwork\Domain\Exceptions\ValueException;

final class ValueExceptionTest extends TestCase
{
    public function testExceptionHasExpectedMessage(): void
    {
        $exception = new ValueException('Invalid value provided');

        $this->assertSame('Invalid value provided', $exception->getMessage());
    }

    public function testExceptionExtendsDomainException(): void
    {
        $exception = new ValueException('Error');

        $this->assertInstanceOf(DomainException::class, $exception);
    }

    public function testExceptionPreservesCode(): void
    {
        $exception = new ValueException('Error', 422);

        $this->assertSame(422, $exception->getCode());
    }
}
