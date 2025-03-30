<?php

namespace App\Service\ExchangeRate;

use App\Entity\ExchangeRate;
use App\Enum\DataProcessor;
use App\Integration\Nbp\NbpApiClient;
use App\Repository\SymbolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class NbpProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly NbpApiClient $client,
        private readonly SymbolRepository $symbolRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {}

    public function supports(DataProcessor $processor): bool
    {
        return $processor === DataProcessor::NBP;
    }

    public function update(array $pairs): void
    {
        foreach ($pairs as [$base, $quote]) {
            if (strtoupper($quote) !== 'PLN') {
                continue;
            }

            try {
                $price = $this->client->getMidRate($base);

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
                $this->logger->error('Error while updating the exchange rate from NBP', [
                    'exception' => $e,
                    'base' => $base,
                    'quote' => $quote,
                ]);
            }
        }

        $this->entityManager->flush();
    }
}
