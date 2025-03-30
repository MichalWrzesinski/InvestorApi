<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Symbol;

final class SymbolCreatedEvent implements EventInterface
{
    public function __construct(
        public readonly Symbol $symbol,
    ) {}
}
