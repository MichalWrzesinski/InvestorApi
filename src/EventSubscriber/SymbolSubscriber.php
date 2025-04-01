<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Symbol;
use App\Service\ExchangeRateSynchronizer;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Symbol::class)]
#[AsEntityListener(event: Events::postFlush, method: 'postFlush', entity: Symbol::class)]
final class SymbolSubscriber implements EventSubscriberInterface
{
    /** @var Symbol[] */
    private array $newSymbols = [];

    public function __construct(
        private readonly ExchangeRateSynchronizer $synchronizer,
    ) {
    }

    /** @param LifecycleEventArgs<EntityManagerInterface> $args  */
    public function postPersist(Symbol $symbol, LifecycleEventArgs $args): void
    {
        $this->newSymbols[] = $symbol;
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
