<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\UserAssetRepository;
use Symfony\Bundle\SecurityBundle\Security;

final class UserAssetProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserAssetRepository $repository,
        private readonly Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();

        if (!$user) {
            return [];
        }

        return $this->repository->findBy(['user' => $user]);
    }
}
