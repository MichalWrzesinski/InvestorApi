<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Security\OwnedByUserInterface;

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

        if (!$user instanceof UserInterface
            || !$resourceClass
            || !is_subclass_of($resourceClass, OwnedByUserInterface::class)
        ) {
            return [];
        }

        $fieldPath = $resourceClass::getUserFieldPath();
        $parts = explode('.', $fieldPath);

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('o')->from($resourceClass, 'o');

        $lastAlias = 'o';
        foreach ($parts as $i => $part) {
            $nextAlias = 'a' . $i;
            $queryBuilder->join("$lastAlias.$part", $nextAlias);
            $lastAlias = $nextAlias;
        }

        $queryBuilder->where("$lastAlias = :user")
            ->setParameter('user', $user);

        /** @var array<int, object> */
        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }
}
