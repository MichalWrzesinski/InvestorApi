<?php

declare(strict_types=1);

namespace App\Integration\Nbp;

interface NbpApiClientInterface
{
    public function getMidRate(string $symbol): float;
}
