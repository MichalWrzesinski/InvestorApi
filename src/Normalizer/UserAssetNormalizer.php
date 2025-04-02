<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Dto\ValueInDefaultCurrencyDtoOutput;
use App\Entity\ExchangeRateLatest;
use App\Entity\User;
use App\Entity\UserAsset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class UserAssetNormalizer implements NormalizerInterface
{
    public function __construct(
        private NormalizerInterface $normalizer,
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UserAsset;
    }

    /** @return array<string, mixed> */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var UserAsset $userAsset */
        $userAsset = $object;
        $symbol = $userAsset->getSymbol();

        $user = $this->security->getUser();

        $quoteSymbol = 'USD';
        if ($user instanceof User && null !== $user->getDefaultQuoteSymbol()) {
            $quoteSymbol = $user->getDefaultQuoteSymbol()->getSymbol();
        }

        $baseSymbol = $symbol->getSymbol();
        $rateRepo = $this->entityManager->getRepository(ExchangeRateLatest::class);

        $convertedValue = null;

        if ($baseSymbol === $quoteSymbol) {
            $convertedValue = $userAsset->getBalance(); // przelicznik 1.0
        } else {
            $direct = $rateRepo->findOneBy([
                'baseSymbol' => $baseSymbol,
                'quoteSymbol' => $quoteSymbol,
            ]);

            if ($direct) {
                $convertedValue = $userAsset->getBalance() * $direct->getPrice();
            } else {
                $viaUsd = $rateRepo->findOneBy([
                    'baseSymbol' => $baseSymbol,
                    'quoteSymbol' => 'USD',
                ]);
                $usdToTarget = $rateRepo->findOneBy([
                    'baseSymbol' => 'USD',
                    'quoteSymbol' => $quoteSymbol,
                ]);

                if ($viaUsd && $usdToTarget) {
                    $convertedValue = $userAsset->getBalance() * $viaUsd->getPrice() * $usdToTarget->getPrice();
                }
            }
        }

        if (null !== $convertedValue) {
            $userAsset->setValueInDefaultCurrency(new ValueInDefaultCurrencyDtoOutput(
                value: round($convertedValue, 6),
                quote: $quoteSymbol
            ));
        }

        return (array) $this->normalizer->normalize($userAsset, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            UserAsset::class => true,
        ];
    }
}
