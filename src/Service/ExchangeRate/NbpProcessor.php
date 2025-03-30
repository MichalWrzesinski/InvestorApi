<?php

namespace App\Service\ExchangeRate;

use App\Enum\DataProcessor;

class NbpProcessor implements ProcessorInterface
{
    public function supports(DataProcessor $processor): bool
    {
        return $processor === DataProcessor::NBP;
    }

    public function update(array $pairs): void
    {
        // TODO: integracja z API NBP
    }
}
