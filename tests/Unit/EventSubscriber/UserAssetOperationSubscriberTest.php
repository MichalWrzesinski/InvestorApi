<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\UserAssetOperation;
use App\EventSubscriber\UserAssetOperationSubscriber;
use App\Service\UserAssetBalanceServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UserAssetOperationSubscriberTest extends TestCase
{
    private UserAssetBalanceServiceInterface&MockObject $balanceService;
    private UserAssetOperationSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->balanceService = $this->createMock(UserAssetBalanceServiceInterface::class);
        $this->subscriber = new UserAssetOperationSubscriber($this->balanceService);
    }

    public function testOnPostPersistAppliesFullAmount(): void
    {
        $operation = $this->createMock(UserAssetOperation::class);
        $operation->method('getAmount')->willReturn(100.0);

        $args = $this->createMock(LifecycleEventArgs::class);

        $this->balanceService->expects($this->once())
            ->method('apply')
            ->with($operation, 100.0);

        $this->subscriber->onPostPersist($operation, $args);
    }

    public function testOnPostUpdateAppliesAmountDifference(): void
    {
        $operation = $this->createMock(UserAssetOperation::class);

        $args = $this->createMock(LifecycleEventArgs::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $unitOfWork = $this->createMock(UnitOfWork::class);

        $args->method('getObjectManager')->willReturn($entityManager);
        $entityManager->method('getUnitOfWork')->willReturn($unitOfWork);

        $unitOfWork->method('getEntityChangeSet')->with($operation)->willReturn([
            'amount' => [100.0, 125.0],
        ]);

        $this->balanceService->expects($this->once())
            ->method('apply')
            ->with($operation, 25.0);

        $this->subscriber->onPostUpdate($operation, $args);
    }

    public function testOnPostUpdateAppliesSoftDelete(): void
    {
        $operation = $this->createMock(UserAssetOperation::class);
        $operation->method('getAmount')->willReturn(50.0);

        $args = $this->createMock(LifecycleEventArgs::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $unitOfWork = $this->createMock(UnitOfWork::class);

        $args->method('getObjectManager')->willReturn($entityManager);
        $entityManager->method('getUnitOfWork')->willReturn($unitOfWork);

        $unitOfWork->method('getEntityChangeSet')->willReturn([
            'deletedAt' => [null, new \DateTimeImmutable()],
        ]);

        $this->balanceService->expects($this->once())
            ->method('apply')
            ->with($operation, -50.0);

        $this->subscriber->onPostUpdate($operation, $args);
    }

    public function testOnPostUpdateDoesNothingWhenNoRelevantChanges(): void
    {
        $operation = $this->createMock(UserAssetOperation::class);

        $args = $this->createMock(LifecycleEventArgs::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $unitOfWork = $this->createMock(UnitOfWork::class);

        $args->method('getObjectManager')->willReturn($entityManager);
        $entityManager->method('getUnitOfWork')->willReturn($unitOfWork);

        $unitOfWork->method('getEntityChangeSet')->willReturn([
            'somethingElse' => ['old', 'new'],
        ]);

        $this->balanceService->expects($this->never())->method('apply');

        $this->subscriber->onPostUpdate($operation, $args);
    }

    public function testOnPostRemoveAppliesNegativeAmount(): void
    {
        $operation = $this->createMock(UserAssetOperation::class);
        $operation->method('getAmount')->willReturn(300.0);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $args = new PostRemoveEventArgs($operation, $entityManager);

        $this->balanceService->expects($this->once())
            ->method('apply')
            ->with($operation, -300.0);

        $this->subscriber->onPostRemove($operation, $args);
    }
}
