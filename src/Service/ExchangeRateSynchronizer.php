<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Symbol;
use App\Entity\ExchangeRate;
use App\Repository\ExchangeRateRepository;
use App\Validator\SymbolPairValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

final class ExchangeRateSynchronizer
{
    public function __construct(
        private readonly SymbolPairValidatorInterface $symbolPairValidator,
        private readonly ExchangeRateRepository $exchangeRateRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function synchronizeFor(Symbol $symbol): void
    {
        foreach ($this->entityManager->getRepository(Symbol::class)->findAll() as $otherSymbol) {
            foreach ($this->getSymmetricSymbolPairs($symbol, $otherSymbol) as $pair) {
                if ($pair['base'] === $pair['quote']) {
                    continue;
                }

                if ($this->shouldInsertExchangeRate($pair['base'], $pair['quote'])) {
                    $exchangeRate = (new ExchangeRate())
                        ->setBase($pair['base'])
                        ->setQuote($pair['quote'])
                        ->setPrice(0.0);

                    $this->entityManager->persist($exchangeRate);
                }
            }
        }

        $this->entityManager->flush();
    }

    private function getSymmetricSymbolPairs(Symbol $symbolA, Symbol $symbolB): array
    {
        return [
            ['base' => $symbolA, 'quote' => $symbolB],
            ['base' => $symbolB, 'quote' => $symbolA],
        ];
    }

    private function shouldInsertExchangeRate(Symbol $base, Symbol $quote): bool
    {
        return !$this->exchangeRateRepository->exists($base, $quote)
            && $this->symbolPairValidator->isValid($base->getType(), $quote->getType());
    }
}
