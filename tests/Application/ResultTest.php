<?php

declare(strict_types=1);

namespace Tests\Application;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\Result;
use SeedWork\Application\ResultError;

/**
 * @internal
 *
 * @coversNothing
 */
final class ResultTest extends TestCase
{
    public function testOkIsOk(): void
    {
        $result = Result::ok();

        $this->assertTrue($result->isOk());
        $this->assertFalse($result->isFailed());
        $this->assertSame([], $result->errors());
    }

    public function testFailedIsFailed(): void
    {
        $errors = [new ResultError('E001', 'Something went wrong')];
        $result = Result::failed($errors);

        $this->assertFalse($result->isOk());
        $this->assertTrue($result->isFailed());
    }

    public function testFailedErrorsAreAccessible(): void
    {
        $error1 = new ResultError('E001', 'First error');
        $error2 = new ResultError('E002', 'Second error');
        $result = Result::failed([$error1, $error2]);

        $errors = $result->errors();

        $this->assertCount(2, $errors);
        $this->assertSame('E001', $errors[0]->code);
        $this->assertSame('First error', $errors[0]->description);
        $this->assertSame('E002', $errors[1]->code);
    }

    public function testOkHasNoErrors(): void
    {
        $result = Result::ok();

        $this->assertEmpty($result->errors());
    }

    public function testFailedWithEmptyErrorsThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Result::failed([]);
    }
}
