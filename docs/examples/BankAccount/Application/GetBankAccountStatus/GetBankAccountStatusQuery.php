<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\GetBankAccountStatus;

use SeedWork\Application\Query;
use SeedWork\Application\ValidationError;
use SeedWork\Application\ValidationErrors;

final readonly class GetBankAccountStatusQuery extends Query
{
    public function __construct(
        public string $accountId
    ) {
        parent::__construct();
    }

    public function validate(): void
    {
        $errors = [];
        if (empty($this->accountId)) {
            $errors[] = new ValidationError('accountId', 'Account ID is required.');
        }
        if (count($errors) > 0) {
            throw new ValidationErrors($errors);
        }
    }
}
