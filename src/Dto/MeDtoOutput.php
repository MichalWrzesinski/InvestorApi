<?php

declare(strict_types=1);

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\Provider\MeProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/me',
            security: 'is_granted("ROLE_USER")',
            provider: MeProvider::class
        ),
    ],
    normalizationContext: ['groups' => ['me:read']],
    output: MeDtoOutput::class,
)]
final class MeDtoOutput
{
    public function __construct(
        #[Groups(['me:read'])]
        public string $email,
        #[Groups(['me:read'])]
        public ?array $defaultQuoteSymbol,
        /** @var array<int, array<string, mixed>> */
        #[Groups(['me:read'])]
        public array $assets,
        #[Groups(['me:read'])]
        public ValueInDefaultCurrencyDtoOutput $totalBalance,
    ) {
    }
}
