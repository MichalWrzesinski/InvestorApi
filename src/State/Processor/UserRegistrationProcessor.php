<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\UserRegistrationInput;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** @implements ProcessorInterface<UserRegistrationInput, User> */
final class UserRegistrationProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    /** @param UserRegistrationInput $data */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        /** @var UserRegistrationInput $data */
        $user = new User();
        $user->setEmail($data->email);
        $user->setActive(true);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $this->hasher->hashPassword($user, $data->password)
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
