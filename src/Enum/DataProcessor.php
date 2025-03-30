<?php

declare(strict_types=1);

namespace App\Enum;

namespace App\Enum;

enum DataProcessor: string
{
    case BINANCE = 'BINANCE';
    case NBP = 'NBP';
    case STOOQ = 'STOOQ';
    case YAHOO = 'YAHOO';
}
