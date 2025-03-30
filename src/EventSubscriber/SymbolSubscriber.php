<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Symbol;
use App\Service\ExchangeRateSynchronizer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

final class SymbolSubscriber implements EventSubscriber
{
    public function __construct(
        private readonly ExchangeRateSynchronizer $synchronizer,
    ) {}

    public function getSubscribedEvents(): array
    {
        return [Events::postPersist];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Symbol) {
            return;
        }

        file_put_contents('symbol_subscriber.log', $entity->getSymbol() . PHP_EOL, FILE_APPEND);

        $this->synchronizer->synchronizeFor($entity);
    }
}
