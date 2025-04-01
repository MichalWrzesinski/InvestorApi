<?php

namespace App\Validator;

use App\Entity\Symbol;

interface SymbolPairValidatorInterface
{
    public function isValid(Symbol $base, Symbol $quote): bool;
}
