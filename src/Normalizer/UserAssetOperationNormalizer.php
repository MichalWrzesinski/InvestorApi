<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Dto\ValueInDefaultCurrencyDtoOutput;
use App\Entity\ExchangeRateLatest;
use App\Entity\User;
use App\Entity\UserAssetOperation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final readonly class UserAssetOperationNormalizer implements NormalizerInterface, UserAssetNormalizerInterface
{
    public function __construct(
        private ObjectNormalizer $normalizer,
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UserAssetOperation;
    }

    /** @return array<string, mixed> */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var UserAssetOperation $operation */
        $operation = $object;
        $userAsset = $operation->getUserAsset();
        $symbol = $userAsset->getSymbol();

        $user = $this->security->getUser();
        $quote = 'USD';
        if ($user instanceof User && $user->getDefaultQuoteSymbol()) {
            $quote = $user->getDefaultQuoteSymbol()->getSymbol();
        }

        $rateRepo = $this->entityManager->getRepository(ExchangeRateLatest::class);
        $base = $symbol->getSymbol();

        $converted = null;

        if ($base === $quote) {
            $converted = $operation->getAmount();
        } else {
            $direct = $rateRepo->findOneBy(['baseSymbol' => $base, 'quoteSymbol' => $quote]);

            if ($direct) {
                $converted = $operation->getAmount() * $direct->getPrice();
            } else {
                $viaUsd = $rateRepo->findOneBy(['baseSymbol' => $base, 'quoteSymbol' => 'USD']);
                $usdToTarget = $rateRepo->findOneBy(['baseSymbol' => 'USD', 'quoteSymbol' => $quote]);

                if ($viaUsd && $usdToTarget) {
                    $converted = $operation->getAmount() * $viaUsd->getPrice() * $usdToTarget->getPrice();
                }
            }
        }

        if (null !== $converted) {
            $operation->setValueInDefaultCurrency(new ValueInDefaultCurrencyDtoOutput(
                value: round($converted, 6),
                quote: $quote
            ));
        }

        return (array) $this->normalizer->normalize($operation, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [UserAssetOperation::class => true];
    }
}
