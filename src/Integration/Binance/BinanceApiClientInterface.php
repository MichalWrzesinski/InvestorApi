<?php

declare(strict_types=1);

namespace App\Integration\Binance;

use App\Enum\SymbolTypeEnum;

interface BinanceApiClientInterface
{
    public function getPriceForPair(SymbolTypeEnum $type, string $base, string $quote): float;
}
