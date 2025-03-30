<?php

namespace App\Service\ExchangeRate;

use App\Entity\ExchangeRate;
use App\Enum\DataProcessor;
use App\Integration\Stooq\StooqApiClient;
use App\Repository\SymbolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class StooqProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly StooqApiClient $client,
        private readonly SymbolRepository $symbolRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {}

    public function supports(DataProcessor $processor): bool
    {
        return $processor === DataProcessor::STOOQ;
    }

    public function update(array $pairs): void
    {
        foreach ($pairs as [$base, $quote]) {
            try {
                $symbolCode = strtolower($base . $quote);
                $price = $this->client->getPriceForSymbol($symbolCode);

                $baseSymbol = $this->symbolRepository->findOneBy(['code' => strtoupper($base)]);
                $quoteSymbol = $this->symbolRepository->findOneBy(['code' => strtoupper($quote)]);

                if (!$baseSymbol || !$quoteSymbol) {
                    continue;
                }

                $exchangeRate = new ExchangeRate();
                $exchangeRate->setBase($baseSymbol);
                $exchangeRate->setQuote($quoteSymbol);
                $exchangeRate->setPrice($price);

                $this->entityManager->persist($exchangeRate);

            } catch (Throwable $e) {
                $this->logger->error('Error while updating the exchange rate from Stooq', [
                    'exception' => $e,
                    'base' => $base,
                    'quote' => $quote,
                ]);
            }
        }

        $this->entityManager->flush();
    }
}
