<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Symbol;

final readonly class SymbolEvent implements EventInterface
{
    public function __construct(
        public Symbol $symbol,
    ) {
    }

    public function getName(): string
    {
        return 'symbol';
    }
}
