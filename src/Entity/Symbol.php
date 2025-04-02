<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Dto\LatestPriceDtoOutput;
use App\Entity\Trait\SoftDeletableTrait;
use App\Entity\Trait\SoftDeletableTraitInterface;
use App\Entity\Trait\TimestampableTrait;
use App\Entity\Trait\TimestampableTraitInterface;
use App\Enum\DataProcessorEnum;
use App\Enum\SymbolTypeEnum;
use App\Repository\SymbolRepository;
use App\State\Processor\SymbolProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SymbolRepository::class)]
#[ORM\Table(name: 'symbol')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Metadata\Get(security: 'is_granted("ROLE_USER")'),
        new Metadata\GetCollection(security: 'is_granted("ROLE_USER")'),
        new Metadata\Post(security: 'is_granted("ROLE_ADMIN")', processor: SymbolProcessor::class),
        new Metadata\Put(security: 'is_granted("ROLE_ADMIN")'),
        new Metadata\Patch(security: 'is_granted("ROLE_ADMIN")'),
        new Metadata\Delete(security: 'is_granted("ROLE_ADMIN")'),
    ],
    normalizationContext: ['groups' => ['symbol:read']],
    denormalizationContext: ['groups' => ['symbol:write']],
    filters: ['symbol.search_filter', 'symbol.order_filter']
)]
#[ApiFilter(SearchFilter::class, properties: [
    'symbol' => 'partial',
    'name' => 'partial',
    'type' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: [
    'symbol',
    'name',
    'type',
])]
class Symbol implements SoftDeletableTraitInterface, TimestampableTraitInterface
{
    use TimestampableTrait;
    use SoftDeletableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['symbol:read'])]
    private ?Uuid $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 15)]
    #[ORM\Column(type: Types::STRING, length: 15, unique: true)]
    #[Groups([
        'symbol:read',
        'symbol:write',
        'exchange_rate:read',
        'user_asset:read',
        'user_asset_operation:read',
    ])]
    private string $symbol;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Groups([
        'symbol:read',
        'symbol:write',
        'exchange_rate:read',
        'user_asset:read',
        'user_asset_operation:read',
    ])]
    private string $name;

    #[Assert\NotBlank]
    #[ORM\Column(enumType: SymbolTypeEnum::class)]
    #[Groups([
        'symbol:read',
        'symbol:write',
        'exchange_rate:read',
        'user_asset:read',
        'user_asset_operation:read',
    ])]
    private SymbolTypeEnum $type;

    #[Assert\NotBlank]
    #[ORM\Column(enumType: DataProcessorEnum::class)]
    #[Groups(['symbol:read', 'symbol:write'])]
    private DataProcessorEnum $processor;

    #[Groups(['symbol:read'])]
    private ?LatestPriceDtoOutput $latestPrice = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = strtoupper($symbol);

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): SymbolTypeEnum
    {
        return $this->type;
    }

    public function setType(SymbolTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getProcessor(): DataProcessorEnum
    {
        return $this->processor;
    }

    public function setProcessor(DataProcessorEnum $processor): self
    {
        $this->processor = $processor;

        return $this;
    }

    public function getLatestPrice(): ?LatestPriceDtoOutput
    {
        return $this->latestPrice;
    }

    public function setLatestPrice(?LatestPriceDtoOutput $latestPrice): void
    {
        $this->latestPrice = $latestPrice;
    }
}
