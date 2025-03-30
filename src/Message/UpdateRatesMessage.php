<?php

declare(strict_types=1);

namespace App\Message;

use App\Enum\DataProcessor;

class UpdateRatesMessage implements MessageInterface
{
    public function __construct(
        public readonly string $processor,
        /** @var array<array{base: string, quote: string}> */
        public readonly array $pairs,
    ) {}

    public function getProcessorEnum(): ?DataProcessor
    {
        return DataProcessor::tryFrom($this->processor);
    }
}
