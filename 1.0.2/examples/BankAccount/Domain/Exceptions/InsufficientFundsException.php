<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Exceptions;

final class InsufficientFundsException extends \DomainException
{
    public static function forWithdrawal(int $balance, int $requested): self
    {
        return new self(
            sprintf('Insufficient funds: balance %d, requested %d', $balance, $requested)
        );
    }
}
