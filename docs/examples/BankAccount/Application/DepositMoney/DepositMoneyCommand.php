<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\DepositMoney;

use SeedWork\Application\Command;
use SeedWork\Application\ValidationError;
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
            $errors[] = new ValidationError('accountId', 'Account ID is required.');
        }
        if ($this->amount <= 0) {
            $errors[] = new ValidationError('amount', 'Amount must be positive.');
        }
        if (empty($this->currency)) {
            $errors[] = new ValidationError('currency', 'Currency is required.');
        }
        if (count($errors) > 0) {
            throw new ValidationErrors($errors);
        }
    }
}
