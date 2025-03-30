<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Symbol;

interface SymbolRepositoryInterface
{
    /**
     * @return Symbol[]
     */
    public function findAll(): array;
}
