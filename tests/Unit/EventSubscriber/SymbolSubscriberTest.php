<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\Symbol;
use App\EventSubscriber\SymbolSubscriber;
use App\Service\ExchangeRateSynchronizerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SymbolSubscriberTest extends TestCase
{
    private ExchangeRateSynchronizerInterface&MockObject $synchronizer;
    private SymbolSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->synchronizer = $this->createMock(ExchangeRateSynchronizerInterface::class);
        $this->subscriber = new SymbolSubscriber($this->synchronizer);
    }

    public function testPostPersistStoresSymbol(): void
    {
        $symbol = new Symbol();
        $args = $this->createMock(LifecycleEventArgs::class);

        $this->subscriber->postPersist($symbol, $args);

        $objectManager = $this->createMock(EntityManagerInterface::class);
        $objectManager->expects($this->once())->method('flush');

        $this->synchronizer->expects($this->once())
            ->method('synchronizeFor')
            ->with($symbol);

        $postFlushArgs = new PostFlushEventArgs($objectManager);
        $this->subscriber->postFlush($postFlushArgs);
    }

    public function testPostFlushDoesNothingWhenNoSymbols(): void
    {
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->synchronizer->expects($this->never())->method('synchronizeFor');
        $objectManager->expects($this->never())->method('flush');

        $this->subscriber->postFlush(new PostFlushEventArgs($objectManager));
    }

    public function testPostFlushClearsQueue(): void
    {
        $symbol1 = new Symbol();
        $symbol2 = new Symbol();

        $args = $this->createMock(LifecycleEventArgs::class);
        $this->subscriber->postPersist($symbol1, $args);
        $this->subscriber->postPersist($symbol2, $args);

        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->synchronizer->expects($this->exactly(2))
            ->method('synchronizeFor')
            ->withConsecutive([$symbol1], [$symbol2]);

        $objectManager->expects($this->once())->method('flush');

        $postFlushArgs = new PostFlushEventArgs($objectManager);
        $this->subscriber->postFlush($postFlushArgs);

        $this->synchronizer->expects($this->never())->method('synchronizeFor');
        $objectManager->expects($this->never())->method('flush');

        $this->subscriber->postFlush($postFlushArgs);
    }
}
