<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Dto\ValueInDefaultCurrencyDtoOutput;
use App\Entity\Trait\SoftDeletableTrait;
use App\Entity\Trait\SoftDeletableTraitInterface;
use App\Entity\Trait\TimestampableTrait;
use App\Entity\Trait\TimestampableTraitInterface;
use App\Enum\AssetOperationTypeEnum;
use App\Repository\UserAssetOperationRepository;
use App\Security\OwnedByUserInterface;
use App\State\Processor\CurrentUserProcessor;
use App\State\Provider\CurrentUserCollectionProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserAssetOperationRepository::class)]
#[ORM\Table(name: 'user_asset_operation')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Metadata\Get(security: 'is_granted("ROLE_USER") and object.getUserAsset().getUser() === user'),
        new Metadata\GetCollection(
            security: 'is_granted("ROLE_USER")',
            provider: CurrentUserCollectionProvider::class
        ),
        new Metadata\Post(
            security: 'is_granted("ROLE_USER")',
            processor: CurrentUserProcessor::class
        ),
        new Metadata\Put(security: 'is_granted("ROLE_USER") and object.getUserAsset().getUser() === user'),
        new Metadata\Patch(security: 'is_granted("ROLE_USER") and object.getUserAsset().getUser() === user'),
        new Metadata\Delete(security: 'is_granted("ROLE_USER") and object.getUserAsset().getUser() === user'),
    ],
    normalizationContext: ['groups' => ['user_asset_operation:read']],
    denormalizationContext: ['groups' => ['user_asset_operation:write']]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'userAsset' => 'exact',
    'type' => 'exact',
    'name' => 'partial',
])]
#[ApiFilter(RangeFilter::class, properties: [
    'amount',
])]
#[ApiFilter(DateFilter::class, properties: [
    'createdAt',
    'updatedAt',
    'deletedAt',
])]
#[ApiFilter(OrderFilter::class, properties: [
    'name',
    'amount',
])]
class UserAssetOperation implements SoftDeletableTraitInterface, TimestampableTraitInterface, OwnedByUserInterface
{
    use SoftDeletableTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['user_asset_operation:read'])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: UserAsset::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['user_asset_operation:read', 'user_asset_operation:write'])]
    private UserAsset $userAsset;

    #[Assert\NotNull]
    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(['user_asset_operation:read', 'user_asset_operation:write'])]
    private float $amount;

    #[Assert\NotNull]
    #[ORM\Column(type: Types::STRING, enumType: AssetOperationTypeEnum::class)]
    #[Groups(['user_asset_operation:read', 'user_asset_operation:write'])]
    private AssetOperationTypeEnum $type;

    #[Groups(['user_asset_operation:read'])]
    private ?ValueInDefaultCurrencyDtoOutput $valueInDefaultCurrency = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUserAsset(): UserAsset
    {
        return $this->userAsset;
    }

    public function setUserAsset(UserAsset $userAsset): self
    {
        $this->userAsset = $userAsset;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getType(): AssetOperationTypeEnum
    {
        return $this->type;
    }

    public function setType(AssetOperationTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public static function getUserFieldPath(): string
    {
        return 'userAsset.user';
    }

    #[Groups(['user_asset_operation:read'])]
    public function getUser(): User
    {
        return $this->getUserAsset()->getUser();
    }

    public function getValueInDefaultCurrency(): ?ValueInDefaultCurrencyDtoOutput
    {
        return $this->valueInDefaultCurrency;
    }

    public function setValueInDefaultCurrency(?ValueInDefaultCurrencyDtoOutput $value): void
    {
        $this->valueInDefaultCurrency = $value;
    }
}
