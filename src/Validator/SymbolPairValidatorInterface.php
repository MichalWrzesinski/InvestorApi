<?php

namespace App\Validator;

use App\Enum\SymbolType;

interface SymbolPairValidatorInterface
{
    public function isValid(SymbolType $baseType, SymbolType $quoteType): bool;
}
