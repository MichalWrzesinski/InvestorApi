<?php

declare(strict_types=1);

namespace App\Generator;

use App\Entity\Symbol;

interface ValidSymbolPairGeneratorInterface
{
    /** @return iterable<array{base: Symbol, quote: Symbol}> */
    public function generate(): iterable;
}
