<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\TransferMoney;

use SeedWork\Application\Command;
use SeedWork\Application\ValidationErrorDetail;
use SeedWork\Application\ValidationErrors;

final readonly class TransferMoneyCommand extends Command
{
    public function __construct(
        public string $fromAccountId,
        public string $toAccountId,
        public int $amount,
        public string $currency
    ) {
        parent::__construct();
    }

    public function validate(): void
    {
        $errors = [];
        if (empty($this->fromAccountId)) {
            $errors[] = new ValidationErrorDetail('from_account_id_required', 'From account ID is required.');
        }
        if (empty($this->toAccountId)) {
            $errors[] = new ValidationErrorDetail('to_account_id_required', 'To account ID is required.');
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
