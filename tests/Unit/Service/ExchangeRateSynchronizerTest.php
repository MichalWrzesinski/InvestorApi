<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\ExchangeRate;
use App\Entity\Symbol;
use App\Enum\SymbolTypeEnum;
use App\Repository\ExchangeRateRepositoryInterface;
use App\Service\ExchangeRateSynchronizer;
use App\Validator\SymbolPairValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ExchangeRateSynchronizerTest extends TestCase
{
    private SymbolPairValidatorInterface&MockObject $symbolPairValidator;
    private ExchangeRateRepositoryInterface&MockObject $exchangeRateRepository;
    private EntityManagerInterface&MockObject $entityManager;

    private ExchangeRateSynchronizer $synchronizer;

    protected function setUp(): void
    {
        $this->symbolPairValidator = $this->createMock(SymbolPairValidatorInterface::class);
        $this->exchangeRateRepository = $this->createMock(ExchangeRateRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->synchronizer = new ExchangeRateSynchronizer(
            $this->symbolPairValidator,
            $this->exchangeRateRepository,
            $this->entityManager
        );
    }

    public function testSynchronizePersistsValidAndNonExistingPairs(): void
    {
        $symbolA = new Symbol();
        $symbolA->setSymbol('USD');
        $symbolA->setType(SymbolTypeEnum::FIAT);

        $symbolB = new Symbol();
        $symbolB->setSymbol('BTC');
        $symbolB->setType(SymbolTypeEnum::CRYPTO);

        $symbolRepo = $this->createMock(EntityRepository::class);
        $symbolRepo->method('findAll')->willReturn([$symbolA, $symbolB]);

        $this->entityManager->method('getRepository')
            ->with(Symbol::class)
            ->willReturn($symbolRepo);

        $this->exchangeRateRepository->method('exists')->willReturn(false);
        $this->symbolPairValidator->method('isValid')->willReturn(true);

        $this->entityManager->expects($this->exactly(2))->method('persist')
            ->with($this->isInstanceOf(ExchangeRate::class));
        $this->entityManager->expects($this->once())->method('flush');

        $this->synchronizer->synchronizeFor($symbolA);
    }

    public function testSynchronizeSkipsInvalidPairs(): void
    {
        $symbolA = new Symbol();
        $symbolA->setSymbol('AAPL');
        $symbolA->setType(SymbolTypeEnum::STOCK);

        $symbolB = new Symbol();
        $symbolB->setSymbol('USD');
        $symbolB->setType(SymbolTypeEnum::FIAT);

        $symbolRepo = $this->createMock(EntityRepository::class);
        $symbolRepo->method('findAll')->willReturn([$symbolA, $symbolB]);

        $this->entityManager->method('getRepository')
            ->with(Symbol::class)
            ->willReturn($symbolRepo);

        $this->exchangeRateRepository->method('exists')->willReturn(false);
        $this->symbolPairValidator->method('isValid')->willReturn(false);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->synchronizer->synchronizeFor($symbolA);
    }

    public function testSynchronizeSkipsAlreadyExistingPairs(): void
    {
        $symbolA = new Symbol();
        $symbolA->setSymbol('BTC');
        $symbolA->setType(SymbolTypeEnum::CRYPTO);

        $symbolB = new Symbol();
        $symbolB->setSymbol('USDT');
        $symbolB->setType(SymbolTypeEnum::CRYPTO);

        $symbolRepo = $this->createMock(EntityRepository::class);
        $symbolRepo->method('findAll')->willReturn([$symbolA, $symbolB]);

        $this->entityManager->method('getRepository')
            ->with(Symbol::class)
            ->willReturn($symbolRepo);

        $this->exchangeRateRepository->method('exists')->willReturn(true);
        $this->symbolPairValidator->method('isValid')->willReturn(true);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->synchronizer->synchronizeFor($symbolA);
    }

    public function testSynchronizeSkipsWhenBaseEqualsQuote(): void
    {
        $symbol = new Symbol();
        $symbol->setSymbol('USD');
        $symbol->setType(SymbolTypeEnum::FIAT);

        $symbolRepo = $this->createMock(EntityRepository::class);
        $symbolRepo->method('findAll')->willReturn([$symbol]);

        $this->entityManager->method('getRepository')
            ->with(Symbol::class)
            ->willReturn($symbolRepo);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->synchronizer->synchronizeFor($symbol);
    }
}
