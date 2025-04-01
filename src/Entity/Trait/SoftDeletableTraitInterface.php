<?php

declare(strict_types=1);

namespace App\Entity\Trait;

interface SoftDeletableTraitInterface
{
    public function isDeleted(): bool;

    public function getDeletedAt(): ?\DateTimeImmutable;
}
