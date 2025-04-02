<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\UserAsset;
use App\Entity\UserAssetOperation;
use App\Service\UserAssetBalanceService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class UserAssetBalanceServiceTest extends TestCase
{
    public function testApplyAddsDiffToUserAssetBalanceAndPersists(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new UserAssetBalanceService($entityManager);

        $userAsset = new UserAsset();
        $userAsset->setBalance(100.0);

        $operation = $this->createMock(UserAssetOperation::class);
        $operation->method('getUserAsset')->willReturn($userAsset);

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($userAsset);

        $entityManager->expects($this->once())
            ->method('flush');

        $service->apply($operation, 25.5);

        $this->assertSame(125.5, $userAsset->getBalance());
    }
}
