<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Dto\LatestPriceDtoOutput;
use App\Entity\ExchangeRateLatest;
use App\Entity\Symbol;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class SymbolNormalizer implements NormalizerInterface
{
    public function __construct(
        private NormalizerInterface $normalizer,
        private EntityManagerInterface $entityManager,
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

        $rate = $this->entityManager->getRepository(ExchangeRateLatest::class)->findOneBy([
            'baseSymbol' => $symbol->getSymbol(),
            'quoteSymbol' => 'USD',
        ]);

        if (null !== $rate) {
            $symbol->setLatestPrice(new LatestPriceDtoOutput(
                value: $rate->getPrice(),
                quote: 'USD'
            ));
        } elseif ('USD' === $symbol->getSymbol()) {
            $symbol->setLatestPrice(new LatestPriceDtoOutput(
                value: 1.0,
                quote: 'USD'
            ));
        }

        $data = $this->normalizer->normalize($symbol, $format, $context);

        return (array) $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Symbol::class => true,
        ];
    }
}
