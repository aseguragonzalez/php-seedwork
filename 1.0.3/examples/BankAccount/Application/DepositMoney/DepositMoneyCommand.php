<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\DepositMoney;

use SeedWork\Application\Command;
use SeedWork\Application\ValidationErrorDetail;
use SeedWork\Application\ValidationErrors;

final readonly class DepositMoneyCommand extends Command
{
    public function __construct(
        public string $accountId,
        public int $amount,
        public string $currency
    ) {
        parent::__construct();
    }

    public function validate(): void
    {
        $errors = [];
        if (empty($this->accountId)) {
            $errors[] = new ValidationErrorDetail('account_id_required', 'Account ID is required.');
        }
        if ($this->amount <= 0) {
            $errors[] = new ValidationErrorDetail('amount_must_be_positive', 'Amount must be positive.');
        }
        if (empty($this->currency)) {
            $errors[] = new ValidationErrorDetail('currency_required', 'Currency is required.');
        }
        if (count($errors) > 0) {
            throw new ValidationErrors($errors);
        }
    }
}
