<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\UserAssetOperation;
use App\Service\UserAssetBalanceService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;

#[AsEntityListener(event: Events::postPersist, method: 'onPostPersist', entity: UserAssetOperation::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onPostUpdate', entity: UserAssetOperation::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onPostRemove', entity: UserAssetOperation::class)]
final class UserAssetOperationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserAssetBalanceService $balanceService,
    ) {}

    /** @param LifecycleEventArgs<EntityManagerInterface> $args  */
    public function onPostPersist(UserAssetOperation $operation, LifecycleEventArgs $args): void
    {
        $this->balanceService->apply($operation, $operation->getAmount());
    }

    /** @param LifecycleEventArgs<EntityManagerInterface> $args */
    public function onPostUpdate(UserAssetOperation $operation, LifecycleEventArgs $args): void
    {
        $changeSet = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($operation);

        if (isset($changeSet['amount'])
            && is_numeric($changeSet['amount'][0])
            && is_numeric($changeSet['amount'][1])
        ) {
            $this->balanceService->apply(
                $operation,
                (float) $changeSet['amount'][1] - (float) $changeSet['amount'][0]
            );
        }

        if (isset($changeSet['deletedAt'])
            && $changeSet['deletedAt'][0] === null
            && $changeSet['deletedAt'][1] !== null
        ) {
            $this->balanceService->apply($operation, -$operation->getAmount());
        }
    }

    public function onPostRemove(UserAssetOperation $operation, PostRemoveEventArgs $args): void
    {
        $this->balanceService->apply($operation, -$operation->getAmount());
    }
}
