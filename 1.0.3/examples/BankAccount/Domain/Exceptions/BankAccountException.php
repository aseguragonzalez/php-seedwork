<?php

declare(strict_types=1);

namespace Examples\BankAccount\Domain\Exceptions;

class BankAccountException extends \DomainException
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
