<?php

declare(strict_types=1);

namespace App\Tests\Unit\State\Provider;

use ApiPlatform\Metadata\GetCollection;
use App\Entity\User;
use App\Entity\UserAsset;
use App\State\Provider\CurrentUserCollectionProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

final class CurrentUserCollectionProviderTest extends TestCase
{
    public function testReturnsUserFilteredEntities(): void
    {
        $user = $this->createMock(User::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $query = $this->createMock(Query::class);
        $query->expects(self::once())
            ->method('getResult')
            ->willReturn(['filtered-entity']);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('join')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

        $operation = new GetCollection(
            uriTemplate: '/api/user_assets',
            class: UserAsset::class
        );

        $provider = new CurrentUserCollectionProvider($entityManager, $security);
        $result = $provider->provide($operation);

        self::assertSame(['filtered-entity'], $result);
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
