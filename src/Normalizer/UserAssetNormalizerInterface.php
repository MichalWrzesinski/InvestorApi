<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\UserAsset;

interface UserAssetNormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function normalize(UserAsset $asset, ?string $format = null, array $context = []): array;
}
