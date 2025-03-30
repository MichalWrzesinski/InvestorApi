<?php

namespace App\Service\ExchangeRate;

use App\Enum\DataProcessor;

class YahooProcessor implements ProcessorInterface
{
    public function supports(DataProcessor $processor): bool
    {
        return $processor === DataProcessor::YAHOO;
    }

    public function update(array $pairs): void
    {
        // TODO: integracja z API YAHOO
    }
}
