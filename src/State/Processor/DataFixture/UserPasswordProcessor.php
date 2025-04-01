<?php

declare(strict_types=1);

namespace App\State\Processor\DataFixture;

use App\Entity\User;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function preProcess(string $id, object $object): void
    {
        if ($object instanceof User) {
            $object->setPassword(
                $this->passwordHasher->hashPassword(
                    $object,
                    $object->getPassword()
                )
            );
        }
    }

    public function postProcess(string $id, object $object): void
    {
    }
}
