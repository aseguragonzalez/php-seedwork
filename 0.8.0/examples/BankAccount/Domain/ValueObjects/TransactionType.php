<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\ValueObjects;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
}
