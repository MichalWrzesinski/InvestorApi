<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Dto\LatestPriceDtoOutput;
use App\Entity\ExchangeRateLatest;
use App\Entity\Symbol;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final readonly class SymbolNormalizer implements NormalizerInterface
{
    public function __construct(
        private ObjectNormalizer $normalizer,
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Symbol;
    }

    /** @return array<string, mixed> */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var Symbol $symbol */
        $symbol = $object;

        $user = $this->security->getUser();

        $quoteSymbolCode = 'USD';
        if ($user instanceof User && null !== $user->getDefaultQuoteSymbol()) {
            $quoteSymbolCode = $user->getDefaultQuoteSymbol()->getSymbol();
        }

        $rateRepo = $this->entityManager->getRepository(ExchangeRateLatest::class);
        $baseCode = $symbol->getSymbol();

        if ($baseCode === $quoteSymbolCode) {
            $symbol->setLatestPrice(new LatestPriceDtoOutput(1.0, $quoteSymbolCode));
        } else {
            $directRate = $rateRepo->findOneBy([
                'baseSymbol' => $baseCode,
                'quoteSymbol' => $quoteSymbolCode,
            ]);

            if (null !== $directRate) {
                $symbol->setLatestPrice(new LatestPriceDtoOutput(
                    value: $directRate->getPrice(),
                    quote: $quoteSymbolCode
                ));
            } else {
                $toUsd = $rateRepo->findOneBy([
                    'baseSymbol' => $baseCode,
                    'quoteSymbol' => 'USD',
                ]);

                $usdToTarget = $rateRepo->findOneBy([
                    'baseSymbol' => 'USD',
                    'quoteSymbol' => $quoteSymbolCode,
                ]);

                if ($toUsd && $usdToTarget) {
                    $convertedPrice = $toUsd->getPrice() * $usdToTarget->getPrice();

                    $symbol->setLatestPrice(new LatestPriceDtoOutput(
                        value: $convertedPrice,
                        quote: $quoteSymbolCode
                    ));
                }
            }
        }

        return (array) $this->normalizer->normalize($symbol, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Symbol::class => true];
    }
}
