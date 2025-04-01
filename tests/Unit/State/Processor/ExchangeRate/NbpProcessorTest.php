<?php

declare(strict_types=1);

namespace App\Tests\Unit\State\Processor\ExchangeRate;

use App\Entity\ExchangeRate;
use App\Entity\Symbol;
use App\Enum\DataProcessorEnum;
use App\Integration\Nbp\NbpApiClientInterface;
use App\Repository\SymbolRepositoryInterface;
use App\State\Processor\ExchangeRate\NbpProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class NbpProcessorTest extends TestCase
{
    private NbpApiClientInterface&MockObject $client;
    private SymbolRepositoryInterface&MockObject $symbolRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private LoggerInterface&MockObject $logger;

    private NbpProcessor $processor;

    protected function setUp(): void
    {
        $this->client = $this->createMock(NbpApiClientInterface::class);
        $this->symbolRepository = $this->createMock(SymbolRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->processor = new NbpProcessor(
            $this->client,
            $this->symbolRepository,
            $this->entityManager,
            $this->logger
        );
    }

    public function testSupportsReturnsTrueForNbp(): void
    {
        $this->assertTrue($this->processor->supports(DataProcessorEnum::NBP));
    }

    public function testSupportsReturnsFalseForOtherProcessors(): void
    {
        $this->assertFalse($this->processor->supports(DataProcessorEnum::BINANCE));
    }

    public function testUpdatePersistsExchangeRateWhenSymbolsExist(): void
    {
        $base = 'USD';
        $quote = 'PLN';
        $price = 4.1234;

        $baseSymbol = new Symbol();
        $baseSymbol->setSymbol($base);

        $quoteSymbol = new Symbol();
        $quoteSymbol->setSymbol($quote);

        $this->client->expects($this->once())
            ->method('getMidRate')
            ->with($base)
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

    public function testUpdateSkipsWhenQuoteIsNotPLN(): void
    {
        $this->client->expects($this->never())->method('getMidRate');
        $this->entityManager->expects($this->once())->method('flush');

        $this->processor->update([['USD', 'EUR']]);
    }

    public function testUpdateSkipsWhenSymbolsAreMissing(): void
    {
        $this->symbolRepository->method('findOneBy')->willReturn(null);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->client->method('getMidRate')->willReturn(4.56);

        $this->processor->update([['USD', 'PLN']]);
    }

    public function testUpdateLogsErrorOnException(): void
    {
        $this->client->method('getMidRate')
            ->willThrowException(new \RuntimeException('NBP API error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Error while updating the exchange rate from NBP',
                $this->arrayHasKey('exception')
            );

        $this->entityManager->expects($this->once())->method('flush');

        $this->processor->update([['USD', 'PLN']]);
    }
}
