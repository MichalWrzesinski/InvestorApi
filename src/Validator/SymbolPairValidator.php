<?php

declare(strict_types=1);

namespace App\Validator;

use App\Enum\SymbolTypeEnum;

final class SymbolPairValidator implements SymbolPairValidatorInterface
{
    public function isValid(SymbolTypeEnum $baseType, SymbolTypeEnum $quoteType): bool
    {
        return match ($baseType) {
            SymbolTypeEnum::FIAT => in_array($quoteType, [SymbolTypeEnum::FIAT, SymbolTypeEnum::CRYPTO], true),
            SymbolTypeEnum::CRYPTO => in_array($quoteType, [SymbolTypeEnum::FIAT, SymbolTypeEnum::CRYPTO], true),
            SymbolTypeEnum::STOCK, SymbolTypeEnum::ETF => SymbolTypeEnum::FIAT === $quoteType,
        };
    }
}
