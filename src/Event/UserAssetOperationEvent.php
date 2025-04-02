<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\UserAssetOperation;

final readonly class UserAssetOperationEvent implements EventInterface
{
    public function __construct(
        public UserAssetOperation $operation,
    ) {
    }

    public function getName(): string
    {
        return 'user_asset_operation';
    }
}
