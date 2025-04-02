<?php

declare(strict_types=1);

namespace App\State\Processor\ExchangeRate;

use App\Entity\ExchangeRate;
use App\Enum\SymbolTypeEnum;
use App\Repository\SymbolRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractExchangeRateProcessor implements ProcessorInterface, ExchangeRateInterface
{
    public function __construct(
        protected SymbolRepositoryInterface $symbolRepository,
        protected EntityManagerInterface $entityManager,
        protected LoggerInterface $logger,
    ) {
    }

    public function update(SymbolTypeEnum $type, string $base, string $quote): void
    {
        try {
            $price = $this->getPriceForPair($type, $base, $quote);

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
            $this->logger->error(sprintf('Error while updating the exchange rate from %s', $this->getSourceName()), [
                'exception' => $exception,
                'base' => $base,
                'quote' => $quote,
            ]);
        }
    }

    abstract protected function getSourceName(): string;

    abstract protected function getPriceForPair(SymbolTypeEnum $type, string $base, string $quote): float;
}
