<?php

declare(strict_types=1);

namespace Examples\BankAccount\Application\TransferMoney;

use SeedWork\Application\Command;
use SeedWork\Application\ValidationError;
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
            $errors[] = new ValidationError('fromAccountId', 'From account ID is required.');
        }
        if (empty($this->toAccountId)) {
            $errors[] = new ValidationError('toAccountId', 'To account ID is required.');
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
