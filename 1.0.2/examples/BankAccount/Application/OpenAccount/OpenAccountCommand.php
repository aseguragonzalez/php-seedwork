<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\OpenAccount;

use SeedWork\Application\Command;
use SeedWork\Application\ValidationErrorDetail;
use SeedWork\Application\ValidationErrors;

final readonly class OpenAccountCommand extends Command
{
    public function __construct(
        public string $currency
    ) {
        parent::__construct();
    }

    public function validate(): void
    {
        $errors = [];
        if (empty($this->currency)) {
            $errors[] = new ValidationErrorDetail('currency_required', 'Currency is required.');
        }
        if (count($errors) > 0) {
            throw new ValidationErrors($errors);
        }
    }
}
