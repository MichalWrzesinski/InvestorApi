<?php

declare(strict_types=1);

namespace App\Service\ExchangeRate;

use App\Enum\DataProcessor;

interface ProcessorInterface
{
    public function supports(DataProcessor $processor): bool;

    public function update(array $pairs): void;
}
