<?php

declare(strict_types=1);

namespace App\Enum;

enum SymbolType: string
{
    case FIAT = 'fiat';
    case CRYPTO = 'crypto';
    case STOCK = 'stock';
    case ETF = 'etf';
}
