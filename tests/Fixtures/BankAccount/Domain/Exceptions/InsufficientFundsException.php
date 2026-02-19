<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\Exceptions;

use Seedwork\Domain\Exceptions\DomainException;

final class InsufficientFundsException extends DomainException
{
    public static function forWithdrawal(int $balance, int $requested): self
    {
        return new self(
            sprintf('Insufficient funds: balance %d, requested %d', $balance, $requested)
        );
    }
}
