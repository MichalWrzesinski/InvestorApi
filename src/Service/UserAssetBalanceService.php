<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\UserAssetOperation;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserAssetBalanceService implements UserAssetBalanceServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function apply(UserAssetOperation $operation, float $diff): void
    {
        $userAsset = $operation->getUserAsset();
        $userAsset->setBalance(
            $userAsset->getBalance() + $diff
        );

        $this->entityManager->persist($userAsset);
        $this->entityManager->flush();
    }
}
