<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;

final readonly class ActivationTokenGenerator implements ActivationTokenGeneratorInterface
{
    public function __construct(
        private string $appSecret,
    ) {
    }

    public function generate(User $user): string
    {
        $createdAt = $user->getCreatedAt();

        if (null === $createdAt) {
            throw new \RuntimeException('User has no creation date set');
        }

        return hash_hmac(
            'sha256',
            sprintf(
                '%s|%d',
                $user->getEmail(),
                $createdAt->getTimestamp(),
            ),
            $this->appSecret
        );
    }

    public function isValid(User $user, string $token): bool
    {
        return hash_equals($this->generate($user), $token);
    }
}
