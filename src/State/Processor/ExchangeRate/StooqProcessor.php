<?php

namespace App\State\Processor\ExchangeRate;

use App\Entity\ExchangeRate;
use App\Enum\DataProcessorEnum;
use App\Enum\SymbolTypeEnum;
use App\Integration\Stooq\StooqApiClientInterface;
use App\Repository\SymbolRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class StooqProcessor implements ProcessorInterface, ExchangeRateInterface
{
    public function __construct(
        private StooqApiClientInterface $client,
        private SymbolRepositoryInterface $symbolRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(DataProcessorEnum $processor): bool
    {
        return DataProcessorEnum::STOOQ === $processor;
    }

    public function update(SymbolTypeEnum $type, string $base, string $quote): void
    {
        try {
            $price = $this->client->getPriceForPair($type, $base, $quote);

            $baseSymbol = $this->symbolRepository->findOneBy(['symbol' => strtoupper($base)]);
            $quoteSymbol = $this->symbolRepository->findOneBy(['symbol' => strtoupper($quote)]);

            if (!$baseSymbol || !$quoteSymbol) {
                return;
            }

            $exchangeRate = new ExchangeRate();
            $exchangeRate->setBase($baseSymbol);
            $exchangeRate->setQuote($quoteSymbol);
            $exchangeRate->setPrice($price);

            $this->entityManager->persist($exchangeRate);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $this->logger->error('Error while updating the exchange rate from Stooq', [
                'exception' => $exception,
                'base' => $base,
                'quote' => $quote,
            ]);
        }
    }
}
