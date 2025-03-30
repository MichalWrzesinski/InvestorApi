<?php

declare(strict_types=1);

namespace App\Enum;

enum SymbolType: string
{
    case FIAT = 'FIAT';
    case CRYPTO = 'CRYPTO';
    case STOCK = 'STOCK';
    case ETF = 'ETF';
}
