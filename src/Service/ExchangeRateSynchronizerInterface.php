<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Symbol;

interface ExchangeRateSynchronizerInterface
{
    public function synchronizeFor(Symbol $symbol): void;
}
