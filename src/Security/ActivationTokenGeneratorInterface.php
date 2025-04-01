<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;

interface ActivationTokenGeneratorInterface
{
    public function generate(User $user): string;

    public function isValid(User $user, string $token): bool;
}
