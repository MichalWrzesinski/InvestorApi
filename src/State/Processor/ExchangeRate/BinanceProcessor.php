<?php

declare(strict_types=1);

namespace App\State\Processor\ExchangeRate;

use App\Entity\ExchangeRate;
use App\Enum\DataProcessor;
use App\Integration\Binance\BinanceApiClientInterface;
use App\Repository\SymbolRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class BinanceProcessor implements ProcessorInterface, ExchangeRateInterface
{
    public function __construct(
        private readonly BinanceApiClientInterface $client,
        private readonly SymbolRepositoryInterface $symbolRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {}

    public function supports(DataProcessor $processor): bool
    {
        return $processor === DataProcessor::BINANCE;
    }

    /** @param array<int, array{string, string}> $pairs */
    public function update(array $pairs): void
    {
        foreach ($pairs as [$base, $quote]) {
            try {
                $price = $this->client->getPriceForPair($base, $quote);

                $baseSymbol = $this->symbolRepository->findOneBy(['symbol' => strtoupper($base)]);
                $quoteSymbol = $this->symbolRepository->findOneBy(['symbol' => strtoupper($quote)]);

                if (!$baseSymbol || !$quoteSymbol) {
                    continue;
                }

                $exchangeRate = new ExchangeRate();
                $exchangeRate->setBase($baseSymbol);
                $exchangeRate->setQuote($quoteSymbol);
                $exchangeRate->setPrice($price);

                $this->entityManager->persist($exchangeRate);

            } catch (Throwable $e) {
                $this->logger->error('Error while updating the exchange rate from Binance', [
                    'exception' => $e,
                    'base' => $base,
                    'quote' => $quote,
                ]);
            }
        }

        $this->entityManager->flush();
    }
}
