<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Symbol;
use App\Enum\SymbolTypeEnum;

final class SymbolPairValidator implements SymbolPairValidatorInterface
{
    public function isValid(Symbol $base, Symbol $quote): bool
    {
        $baseType = $base->getType();
        $quoteType = $quote->getType();

        return match ($baseType) {
            SymbolTypeEnum::FIAT => SymbolTypeEnum::FIAT === $quoteType,
            SymbolTypeEnum::CRYPTO => SymbolTypeEnum::FIAT === $quoteType && 'USD' === $quote->getSymbol(),
            SymbolTypeEnum::STOCK, SymbolTypeEnum::ETF => SymbolTypeEnum::FIAT === $quoteType && 'USD' === $quote->getSymbol(),
        };
    }
}
