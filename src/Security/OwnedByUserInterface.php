<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;

interface OwnedByUserInterface
{
    public static function getUserFieldPath(): string;

    public function getUser(): User;
}
