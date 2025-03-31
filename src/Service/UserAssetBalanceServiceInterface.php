<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\UserAssetOperation;

interface UserAssetBalanceServiceInterface
{
    public function apply(UserAssetOperation $operation, float $diff): void;
}
