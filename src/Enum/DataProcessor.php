<?php

declare(strict_types=1);

namespace App\Enum;

namespace App\Enum;

enum DataProcessor: string
{
    case BINANCE = 'binance';
    case NBP = 'nbp';
    case STOOQ = 'stooq';
    case YAHOO = 'yahoo';
}
