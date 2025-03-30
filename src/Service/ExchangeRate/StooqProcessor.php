<?php

namespace App\Service\ExchangeRate;

use App\Enum\DataProcessor;

class StooqProcessor implements ProcessorInterface
{
    public function supports(DataProcessor $processor): bool
    {
        return $processor === DataProcessor::STOOQ;
    }

    public function update(array $pairs): void
    {
        // TODO: integracja z API STOOQ
    }
}
