<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/** @implements ProviderInterface<object> */
final class CurrentUserCollectionProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    /** @return array<int, object> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();

        /** @var class-string<object> $resourceClass */
        $resourceClass = $operation->getClass();

        if (!$user instanceof UserInterface || !$resourceClass) {
            return [];
        }

        $repository = $this->entityManager->getRepository($resourceClass);

        return $repository->findBy(['user' => $user]);
    }
}
