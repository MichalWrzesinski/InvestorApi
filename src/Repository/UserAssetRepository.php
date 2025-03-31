<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserAsset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<UserAsset> */
final class UserAssetRepository extends ServiceEntityRepository implements UserAssetRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAsset::class);
    }
}
