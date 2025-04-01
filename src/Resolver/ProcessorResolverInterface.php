<?php

declare(strict_types=1);

namespace App\Resolver;

interface ProcessorResolverInterface
{
    public function resolve(string $type): ?string;
}
