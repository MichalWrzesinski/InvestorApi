<?php

declare(strict_types=1);

namespace App\Validator;

use App\Enum\SymbolType;

final class SymbolPairValidator implements SymbolPairValidatorInterface
{
    public function isValid(SymbolType $baseType, SymbolType $quoteType): bool
    {
        return match ($baseType) {
            SymbolType::FIAT => in_array($quoteType, [SymbolType::FIAT, SymbolType::CRYPTO], true),
            SymbolType::CRYPTO => in_array($quoteType, [SymbolType::FIAT, SymbolType::CRYPTO], true),
            SymbolType::STOCK, SymbolType::ETF => $quoteType === SymbolType::FIAT,
        };
    }
}
