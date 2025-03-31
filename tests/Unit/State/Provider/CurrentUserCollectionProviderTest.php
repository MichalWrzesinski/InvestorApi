<?php

declare(strict_types=1);

namespace App\Tests\Unit\State\Provider;

use ApiPlatform\Metadata\GetCollection;
use App\Entity\User;
use App\Entity\UserAsset;
use App\State\Provider\CurrentUserCollectionProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

final class CurrentUserCollectionProviderTest extends TestCase
{
    public function testReturnsUserFilteredEntities(): void
    {
        $user = $this->createMock(User::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())
            ->method('findBy')
            ->with(['user' => $user])
            ->willReturn(['filtered-entity']);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')
            ->with(UserAsset::class)
            ->willReturn($repository);

        $operation = new GetCollection(
            uriTemplate: '/api/user_assets',
            class: UserAsset::class
        );

        $provider = new CurrentUserCollectionProvider($entityManager, $security);
        $result = $provider->provide($operation);

        $this->assertSame(['filtered-entity'], $result);
    }

    public function testReturnsEmptyArrayIfNotLoggedIn(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $operation = new GetCollection(
            uriTemplate: '/api/user_assets',
            class: UserAsset::class
        );

        $provider = new CurrentUserCollectionProvider($entityManager, $security);
        $result = $provider->provide($operation);

        $this->assertSame([], $result);
    }
}
