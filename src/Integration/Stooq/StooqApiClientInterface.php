<?php

declare(strict_types=1);

namespace App\Integration\Stooq;

interface StooqApiClientInterface
{
    public function getPriceForSymbol(string $symbol): float;
}

