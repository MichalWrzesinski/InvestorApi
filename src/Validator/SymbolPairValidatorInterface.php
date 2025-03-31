<?php

namespace App\Validator;

use App\Enum\SymbolTypeEnum;

interface SymbolPairValidatorInterface
{
    public function isValid(SymbolTypeEnum $baseType, SymbolTypeEnum $quoteType): bool;
}
