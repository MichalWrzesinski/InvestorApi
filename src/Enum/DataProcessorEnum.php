<?php

declare(strict_types=1);

namespace App\Enum;

enum DataProcessorEnum: string
{
    case BINANCE = 'BINANCE';
    case NBP = 'NBP';
    case STOOQ = 'STOOQ';
    case YAHOO = 'YAHOO';
}
