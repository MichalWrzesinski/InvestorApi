<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use DateTimeImmutable;

interface TimestampableTraitInterface
{
    public function getCreatedAt(): ?DateTimeImmutable;

    public function getUpdatedAt(): ?DateTimeImmutable;
}
