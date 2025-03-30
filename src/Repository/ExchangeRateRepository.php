<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExchangeRate;
use App\Entity\Symbol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExchangeRate>
 */
class ExchangeRateRepository extends ServiceEntityRepository
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

    public function findAllUniquePairsGroupedByProcessor(): array
    {
        return $this->createQueryBuilder('e')
            ->select(
                'IDENTITY(e.base) AS base_id',
                'IDENTITY(e.quote) AS quote_id',
                's1.code AS base_code',
                's2.code AS quote_code',
                'e.processor'
            )
            ->join('e.base', 's1')
            ->join('e.quote', 's2')
            ->groupBy('e.base, e.quote, e.processor')
            ->getQuery()
            ->getArrayResult();
    }
}
