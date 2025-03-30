<?php

namespace App\Service\ExchangeRate;

use App\Enum\DataProcessor;

class BinanceProcessor implements ProcessorInterface
{
    public function supports(DataProcessor $processor): bool
    {
        return $processor === DataProcessor::BINANCE;
    }

    public function update(array $pairs): void
    {
        // TODO: integracja z API BINANCE
    }
}
