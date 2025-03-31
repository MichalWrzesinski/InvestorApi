<?php

declare(strict_types=1);

namespace App\Tests\Unit\State\Processor\ExchangeRate;

use App\Entity\ExchangeRate;
use App\Entity\Symbol;
use App\Enum\DataProcessorEnum;
use App\Integration\Stooq\StooqApiClientInterface;
use App\Repository\SymbolRepositoryInterface;
use App\State\Processor\ExchangeRate\StooqProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class StooqProcessorTest extends TestCase
{
    private StooqApiClientInterface&MockObject $client;
    private SymbolRepositoryInterface&MockObject $symbolRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private LoggerInterface&MockObject $logger;

    private StooqProcessor $processor;

    protected function setUp(): void
    {
        $this->client = $this->createMock(StooqApiClientInterface::class);
        $this->symbolRepository = $this->createMock(SymbolRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->processor = new StooqProcessor(
            $this->client,
            $this->symbolRepository,
            $this->entityManager,
            $this->logger
        );
    }

    public function testSupportsReturnsTrueForStooq(): void
    {
        $this->assertTrue($this->processor->supports(DataProcessorEnum::STOOQ));
    }

    public function testSupportsReturnsFalseForOtherProcessors(): void
    {
        $this->assertFalse($this->processor->supports(DataProcessorEnum::NBP));
    }

    public function testUpdatePersistsExchangeRateWhenSymbolsExist(): void
    {
        $base = 'AAPL';
        $quote = 'USD';
        $price = 181.32;
        $symbolCode = strtolower($base . $quote);

        $baseSymbol = new Symbol();
        $baseSymbol->setSymbol($base);

        $quoteSymbol = new Symbol();
        $quoteSymbol->setSymbol($quote);

        $this->client->expects($this->once())
            ->method('getPriceForSymbol')
            ->with($symbolCode)
            ->willReturn($price);

        $this->symbolRepository->method('findOneBy')
            ->willReturnCallback(fn(array $criteria) => match ($criteria['symbol']) {
                $base => $baseSymbol,
                $quote => $quoteSymbol,
                default => null,
            });

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (ExchangeRate $rate) use ($baseSymbol, $quoteSymbol, $price) {
                return $rate->getBase() === $baseSymbol &&
                    $rate->getQuote() === $quoteSymbol &&
                    $rate->getPrice() === $price;
            }));

        $this->entityManager->expects($this->once())->method('flush');

        $this->processor->update([[$base, $quote]]);
    }

    public function testUpdateSkipsWhenSymbolsAreMissing(): void
    {
        $this->symbolRepository->method('findOneBy')->willReturn(null);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->processor->update([['AAPL', 'USD']]);
    }

    public function testUpdateLogsErrorOnException(): void
    {
        $this->client->method('getPriceForSymbol')
            ->willThrowException(new RuntimeException('API error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Error while updating the exchange rate from Stooq',
                $this->arrayHasKey('exception')
            );

        $this->entityManager->expects($this->once())->method('flush');

        $this->processor->update([['AAPL', 'USD']]);
    }
}
