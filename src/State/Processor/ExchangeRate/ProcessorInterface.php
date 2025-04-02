<?php

declare(strict_types=1);

namespace App\State\Processor\ExchangeRate;

use App\Enum\DataProcessorEnum;
use App\Enum\SymbolTypeEnum;

interface ProcessorInterface
{
    public function supports(DataProcessorEnum $processor): bool;

    public function update(SymbolTypeEnum $type, string $base, string $quote): void;
}
