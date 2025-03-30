<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExchangeRate;
use App\Entity\Symbol;

/**
 * @method ExchangeRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExchangeRate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExchangeRate[]    findAll()
 * @method ExchangeRate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
interface ExchangeRateRepositoryInterface
{
    public function exists(Symbol $base, Symbol $quote): bool;
}
