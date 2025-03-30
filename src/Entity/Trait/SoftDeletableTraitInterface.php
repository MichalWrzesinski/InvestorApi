<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use DateTimeImmutable;

interface SoftDeletableTraitInterface
{
    public function isDeleted(): bool;

    public function getDeletedAt(): ?DateTimeImmutable;
}
