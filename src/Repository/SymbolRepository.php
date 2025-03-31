<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Symbol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Symbol> */
final class SymbolRepository extends ServiceEntityRepository implements SymbolRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Symbol::class);
    }
}
