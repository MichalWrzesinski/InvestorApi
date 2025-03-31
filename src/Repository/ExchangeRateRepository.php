<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExchangeRate;
use App\Entity\Symbol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ExchangeRate> */
final class ExchangeRateRepository extends ServiceEntityRepository implements ExchangeRateRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRate::class);
    }

    public function exists(Symbol $base, Symbol $quote): bool
    {
        return $this->createQueryBuilder('er')
                ->select('1')
                ->where('er.base = :base')
                ->andWhere('er.quote = :quote')
                ->setParameter('base', $base)
                ->setParameter('quote', $quote)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult() !== null;
    }
}
