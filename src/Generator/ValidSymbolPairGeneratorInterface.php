<?php

declare(strict_types=1);

namespace App\Generator;

interface ValidSymbolPairGeneratorInterface
{
    public function generate(): iterable;
}
