<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Symbol;
use App\Service\ExchangeRateSynchronizer;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;

final class SymbolSubscriber implements EventSubscriber
{
    private array $newSymbols = [];

    public function __construct(
        private readonly ExchangeRateSynchronizer $synchronizer,
    ) {}

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postFlush,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Symbol) {
            $this->newSymbols[] = $entity;
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if (empty($this->newSymbols)) {
            return;
        }

        $symbolsToProcess = $this->newSymbols;
        $this->newSymbols = [];

        foreach ($symbolsToProcess as $symbol) {
            $this->synchronizer->synchronizeFor($symbol);
        }

        $args->getObjectManager()->flush();
    }
}
