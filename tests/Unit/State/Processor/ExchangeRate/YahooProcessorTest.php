<?php

declare(strict_types=1);

namespace App\Tests\Unit\State\Processor\ExchangeRate;

use App\Entity\ExchangeRate;
use App\Entity\Symbol;
use App\Enum\DataProcessorEnum;
use App\Integration\Yahoo\YahooApiClientInterface;
use App\Repository\SymbolRepositoryInterface;
use App\State\Processor\ExchangeRate\YahooProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class YahooProcessorTest extends TestCase
{
    private YahooApiClientInterface&MockObject $client;
    private SymbolRepositoryInterface&MockObject $symbolRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private LoggerInterface&MockObject $logger;

    private YahooProcessor $processor;

    protected function setUp(): void
    {
        $this->client = $this->createMock(YahooApiClientInterface::class);
        $this->symbolRepository = $this->createMock(SymbolRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->processor = new YahooProcessor(
            $this->client,
            $this->symbolRepository,
            $this->entityManager,
            $this->logger
        );
    }

    public function testSupportsReturnsTrueForYahoo(): void
    {
        $this->assertTrue($this->processor->supports(DataProcessorEnum::YAHOO));
    }

    public function testSupportsReturnsFalseForOtherProcessors(): void
    {
        $this->assertFalse($this->processor->supports(DataProcessorEnum::BINANCE));
    }

    public function testUpdatePersistsExchangeRateWhenSymbolsExist(): void
    {
        $base = 'AAPL';
        $quote = 'USD';
        $price = 171.42;

        $baseSymbol = new Symbol();
        $baseSymbol->setSymbol($base);

        $quoteSymbol = new Symbol();
        $quoteSymbol->setSymbol($quote);

        $this->client->expects($this->once())
            ->method('getPriceForSymbol')
            ->with(strtolower($base.$quote))
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
            ->willThrowException(new \RuntimeException('API error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Error while updating the exchange rate from Yahoo',
                $this->arrayHasKey('exception')
            );

        $this->entityManager->expects($this->once())->method('flush');

        $this->processor->update([['AAPL', 'USD']]);
    }
}
