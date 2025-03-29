<?php

declare(strict_types=1);

namespace App\DataFixtures\Processor;

use App\Entity\User;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function preProcess(string $id, object $object): void
    {
        if ($object instanceof User) {
            $plainPassword = $object->getPassword();
            $object->setPassword(
                $this->passwordHasher->hashPassword($object, $plainPassword)
            );
        }
    }

    public function postProcess(string $id, object $object): void
    {
    }
}
