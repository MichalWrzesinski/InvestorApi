<?php

declare(strict_types=1);

namespace App\Integration\Yahoo;

interface YahooApiClientInterface
{
    public function getPriceForSymbol(string $symbol): float;
}

