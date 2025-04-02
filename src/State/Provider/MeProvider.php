<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\MeDtoOutput;
use App\Dto\ValueInDefaultCurrencyDtoOutput;
use App\Entity\User;
use App\Entity\UserAsset;
use App\Normalizer\UserAssetNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/** @implements ProviderInterface<MeDtoOutput> */
final readonly class MeProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserAssetNormalizer $userAssetNormalizer,
        private Security $security,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MeDtoOutput
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $quote = $user->getDefaultQuoteSymbol()?->getSymbol() ?? 'USD';

        $assets = $this->entityManager
            ->getRepository(UserAsset::class)
            ->findBy(['user' => $user]);

        $normalizedAssets = [];
        $total = 0.0;

        foreach ($assets as $asset) {
            $normalized = $this->userAssetNormalizer->normalize($asset, null, $context);

            $value = $asset->getValueInDefaultCurrency()->value ?? 0.0;
            $total += $value;

            $normalizedAssets[] = $normalized;
        }

        $normalizedQuoteSymbol = null;
        if (null !== $user->getDefaultQuoteSymbol()) {
            $normalized = $this->normalizer->normalize(
                $user->getDefaultQuoteSymbol(),
                null,
                $context
            );

            if (is_array($normalized)) {
                $normalizedQuoteSymbol = $normalized;
            }
        }

        return new MeDtoOutput(
            email: $user->getEmail(),
            defaultQuoteSymbol: $normalizedQuoteSymbol,
            assets: $normalizedAssets,
            totalBalance: new ValueInDefaultCurrencyDtoOutput(round($total, 6), $quote)
        );
    }
}
