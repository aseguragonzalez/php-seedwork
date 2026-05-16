<?php

declare(strict_types=1);

namespace Tests\Application;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\ValidationErrorDetail;
use SeedWork\Application\ValidationErrors;

final class ValidationErrorsTest extends TestCase
{
    public function testValidationErrorDetailStoresCodeAndMessage(): void
    {
        $error = new ValidationErrorDetail('email_invalid', 'must be a valid email');

        self::assertSame('email_invalid', $error->code);
        self::assertSame('must be a valid email', $error->message);
    }

    public function testValidationErrorsStoresErrors(): void
    {
        $errors = [
            new ValidationErrorDetail('email_invalid', 'must be a valid email'),
            new ValidationErrorDetail('name_required', 'is required'),
        ];

        $exception = new ValidationErrors($errors);

        self::assertSame($errors, $exception->errors);
    }

    public function testValidationErrorsHasFixedMessage(): void
    {
        $exception = new ValidationErrors([new ValidationErrorDetail('field_invalid', 'message')]);

        self::assertSame('Validation errors', $exception->getMessage());
    }

    public function testValidationErrorsIsAnException(): void
    {
        $exception = new ValidationErrors([]);

        self::assertInstanceOf(\Exception::class, $exception);
    }
}
