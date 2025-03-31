<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\UserAssetOperation;

final class UserAssetOperationEvent implements EventInterface
{
    public function __construct(
        public readonly UserAssetOperation $operation,
    ) {}

    public function getName(): string
    {
        return 'user_asset_operation.created';
    }
}
