<?php

declare(strict_types=1);

namespace App\Integration\Stooq;

use App\Enum\SymbolTypeEnum;

interface StooqApiClientInterface
{
    public function getPriceForPair(SymbolTypeEnum $type, string $base, string $quote): float;
}
