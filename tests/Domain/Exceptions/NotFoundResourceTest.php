<?php

declare(strict_types=1);

namespace Tests\Domain\Exceptions;

use PHPUnit\Framework\TestCase;
use Seedwork\Domain\EntityId;
use Seedwork\Domain\Exceptions\DomainException;
use Seedwork\Domain\Exceptions\NotFoundResource;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;

final class NotFoundResourceTest extends TestCase
{
    public function testExceptionHasExpectedMessageWhenIdProvided(): void
    {
        $id = BankAccountId::fromString('acc-123');
        $exception = new NotFoundResource('BankAccount', $id);

        $this->assertSame("Resource 'BankAccount' not found for id 'acc-123'", $exception->getMessage());
    }

    public function testExceptionHasExpectedMessageWhenIdOmitted(): void
    {
        $exception = new NotFoundResource('Order');

        $this->assertSame("Resource 'Order' not found", $exception->getMessage());
    }

    public function testExceptionExtendsDomainException(): void
    {
        $exception = new NotFoundResource('BankAccount');

        $this->assertInstanceOf(DomainException::class, $exception);
    }

    public function testExceptionPreservesCode(): void
    {
        $exception = new NotFoundResource('BankAccount', null, 404);

        $this->assertSame(404, $exception->getCode());
    }
}
