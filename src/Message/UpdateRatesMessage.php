<?php

declare(strict_types=1);

namespace App\Message;

use App\Enum\DataProcessorEnum;
use App\Enum\SymbolTypeEnum;

final readonly class UpdateRatesMessage implements MessageInterface
{
    public function __construct(
        public string $type,
        public string $processor,
        public string $base,
        public string $quote,
    ) {
    }

    public function getTypeEnum(): ?SymbolTypeEnum
    {
        return SymbolTypeEnum::tryFrom($this->type);
    }

    public function getProcessorEnum(): ?DataProcessorEnum
    {
        return DataProcessorEnum::tryFrom($this->processor);
    }
}
