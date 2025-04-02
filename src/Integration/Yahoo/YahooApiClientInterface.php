<?php

declare(strict_types=1);

namespace App\Integration\Yahoo;

use App\Enum\SymbolTypeEnum;

interface YahooApiClientInterface
{
    public function getPriceForPair(SymbolTypeEnum $type, string $base, string $quote): float;
}
