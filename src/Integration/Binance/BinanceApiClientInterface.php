<?php

declare(strict_types=1);

namespace App\Integration\Binance;

interface BinanceApiClientInterface
{
    public function getPriceForPair(string $base, string $quote): float;
}
