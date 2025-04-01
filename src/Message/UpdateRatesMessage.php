<?php

declare(strict_types=1);

namespace App\Message;

use App\Enum\DataProcessorEnum;

class UpdateRatesMessage implements MessageInterface
{
    public function __construct(
        public readonly string $processor,
        /** @var array<array{base: string, quote: string}> */
        public readonly array $pairs,
    ) {
    }

    public function getProcessorEnum(): ?DataProcessorEnum
    {
        return DataProcessorEnum::tryFrom($this->processor);
    }
}
