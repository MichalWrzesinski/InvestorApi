<?php

declare(strict_types=1);

namespace App\State\Processor\ExchangeRate;

use App\Enum\DataProcessorEnum;

interface ProcessorInterface
{
    public function supports(DataProcessorEnum $processor): bool;

    /** @param array<int, array{string, string}> $pairs */
    public function update(array $pairs): void;
}
