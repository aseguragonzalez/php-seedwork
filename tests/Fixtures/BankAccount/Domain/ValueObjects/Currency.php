<?php

declare(strict_types=1);

namespace Tests\Fixtures\BankAccount\Domain\ValueObjects;

enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case JPY = 'JPY';
    case AUD = 'AUD';
    case CAD = 'CAD';
    case CHF = 'CHF';
}
