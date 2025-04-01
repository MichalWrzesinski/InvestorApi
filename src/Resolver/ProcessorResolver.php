<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Enum\DataProcessorEnum;
use App\Enum\SymbolTypeEnum;

final readonly class ProcessorResolver implements ProcessorResolverInterface
{
    public function resolve(string $type): ?string
    {
        return match ($type) {
            SymbolTypeEnum::FIAT->value => DataProcessorEnum::STOOQ->value,
            SymbolTypeEnum::CRYPTO->value => DataProcessorEnum::BINANCE->value,
            SymbolTypeEnum::STOCK->value, SymbolTypeEnum::ETF->value => DataProcessorEnum::YAHOO->value,
            default => null,
        };
    }
}
