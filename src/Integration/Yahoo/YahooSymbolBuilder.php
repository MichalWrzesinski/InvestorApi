<?php

declare(strict_types=1);

namespace App\Integration\Yahoo;

use App\Enum\SymbolTypeEnum;

final readonly class YahooSymbolBuilder
{
    public function build(SymbolTypeEnum $type, string $base, ?string $quote): string
    {
        return match ($type) {
            SymbolTypeEnum::FIAT => sprintf('%s%s=X', strtoupper($base), strtoupper((string) $quote)),
            SymbolTypeEnum::CRYPTO => sprintf('%s-%s', strtoupper($base), strtoupper((string) $quote)),
            SymbolTypeEnum::STOCK, SymbolTypeEnum::ETF => strtoupper($base),
        };
    }
}
