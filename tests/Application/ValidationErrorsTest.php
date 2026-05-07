<?php

declare(strict_types=1);

namespace Tests\Application;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\ValidationError;
use SeedWork\Application\ValidationErrors;

final class ValidationErrorsTest extends TestCase
{
    public function testValidationErrorStoresFieldAndMessage(): void
    {
        $error = new ValidationError('email', 'must be a valid email');

        self::assertSame('email', $error->field);
        self::assertSame('must be a valid email', $error->message);
    }

    public function testValidationErrorsStoresErrors(): void
    {
        $errors = [
            new ValidationError('email', 'must be a valid email'),
            new ValidationError('name', 'is required'),
        ];

        $exception = new ValidationErrors($errors);

        self::assertSame($errors, $exception->errors);
    }

    public function testValidationErrorsHasFixedMessage(): void
    {
        $exception = new ValidationErrors([new ValidationError('field', 'message')]);

        self::assertSame('Validation errors', $exception->getMessage());
    }

    public function testValidationErrorsIsAnException(): void
    {
        $exception = new ValidationErrors([]);

        self::assertInstanceOf(\Exception::class, $exception);
    }
}
