<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final readonly class LatestPriceDtoOutput
{
    public function __construct(
        #[Groups(['symbol:read'])]
        public float $value,
        #[Groups(['symbol:read'])]
        public string $quote,
    ) {
    }
}
