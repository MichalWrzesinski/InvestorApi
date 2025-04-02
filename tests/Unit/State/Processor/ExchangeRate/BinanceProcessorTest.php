<?php

declare(strict_types=1);

namespace App\Tests\Unit\State\Processor\ExchangeRate;

use App\Entity\ExchangeRate;
use App\Entity\Symbol;
use App\Enum\DataProcessorEnum;
use App\Enum\SymbolTypeEnum;
use App\Integration\Binance\BinanceApiClientInterface;
use App\Repository\SymbolRepositoryInterface;
use App\State\Processor\ExchangeRate\BinanceProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class BinanceProcessorTest extends TestCase
{
    private BinanceApiClientInterface&MockObject $client;
    private SymbolRepositoryInterface&MockObject $symbolRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private LoggerInterface&MockObject $logger;

    private BinanceProcessor $processor;

    protected function setUp(): void
    {
        $this->client = $this->createMock(BinanceApiClientInterface::class);
        $this->symbolRepository = $this->createMock(SymbolRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->processor = new BinanceProcessor(
            $this->client,
            $this->symbolRepository,
            $this->entityManager,
            $this->logger
        );
    }

    public function testSupportsReturnsTrueForBinance(): void
    {
        $this->assertTrue($this->processor->supports(DataProcessorEnum::BINANCE));
    }

    public function testSupportsReturnsFalseForOtherProcessors(): void
    {
        $this->assertFalse($this->processor->supports(DataProcessorEnum::YAHOO));
    }

    public function testUpdatePersistsExchangeRateWhenSymbolsExist(): void
    {
        $type = SymbolTypeEnum::FIAT;
        $base = 'BTC';
        $quote = 'USDT';
        $price = 12345.67;

        $baseSymbol = new Symbol();
        $baseSymbol->setSymbol($base);

        $quoteSymbol = new Symbol();
        $quoteSymbol->setSymbol($quote);

        $this->client->expects($this->once())
            ->method('getPriceForPair')
            ->with($type, $base, $quote)
            ->willReturn($price);

        $this->symbolRepository->method('findOneBy')
            ->willReturnCallback(function (array $criteria) use ($base, $quote, $baseSymbol, $quoteSymbol) {
                return $criteria['symbol'] === $base ? $baseSymbol :
                    ($criteria['symbol'] === $quote ? $quoteSymbol : null);
            });

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (ExchangeRate $rate) use ($baseSymbol, $quoteSymbol, $price) {
                return $rate->getBase() === $baseSymbol
                    && $rate->getQuote() === $quoteSymbol
                    && $rate->getPrice() === $price;
            }));

        $this->entityManager->expects($this->once())->method('flush');

        $this->processor->update($type, $base, $quote);
    }

    public function testUpdateSkipsWhenSymbolsAreMissing(): void
    {
        $type = SymbolTypeEnum::FIAT;
        $base = 'BTC';
        $quote = 'USDT';

        $this->symbolRepository->method('findOneBy')->willReturn(null);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->processor->update($type, $base, $quote);
    }

    public function testUpdateLogsErrorOnException(): void
    {
        $type = SymbolTypeEnum::FIAT;
        $base = 'BTC';
        $quote = 'USDT';

        $this->client->method('getPriceForPair')
            ->willThrowException(new \RuntimeException('API error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Error while updating the exchange rate from Binance',
                $this->arrayHasKey('exception')
            );

        $this->entityManager->expects($this->never())->method('flush');

        $this->processor->update($type, $base, $quote);
    }
}
