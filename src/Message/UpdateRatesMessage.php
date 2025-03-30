<?php

namespace App\Message;

use App\Enum\DataProcessor;

class UpdateRatesMessage implements MessageInterface
{
    public function __construct(
        public readonly string $processor,
        public readonly array $pairs,
    ) {}

    public function getProcessorEnum(): ?DataProcessor
    {
        return DataProcessor::tryFrom($this->processor);
    }
}
