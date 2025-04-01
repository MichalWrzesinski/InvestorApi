<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserAssetOperation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<UserAssetOperation> */
final class UserAssetOperationRepository extends ServiceEntityRepository implements UserAssetOperationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAssetOperation::class);
    }
}
