<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final readonly class ValueInDefaultCurrencyDtoOutput
{
    public function __construct(
        #[Groups(['user_asset:read', 'user_asset_operation:read'])]
        public float $value,
        #[Groups(['user_asset:read', 'user_asset_operation:read'])]
        public string $quote,
    ) {
    }
}
