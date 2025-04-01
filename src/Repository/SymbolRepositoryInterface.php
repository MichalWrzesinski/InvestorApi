<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Symbol;

/**
 * @method Symbol|null find($id, $lockMode = null, $lockVersion = null)
 * @method Symbol[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 */
interface SymbolRepositoryInterface
{
    /**
     * @return Symbol[]
     */
    public function findAll(): array;

    /**
     * @param array<string, mixed>       $criteria
     * @param array<string, string>|null $orderBy
     *
     * @return Symbol|null
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?object;
}
