<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\SoftDeletableTrait;
use App\Entity\Trait\SoftDeletableTraitInterface;
use App\Entity\Trait\TimestampableTrait;
use App\Entity\Trait\TimestampableTraitInterface;
use App\Repository\UserAssetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata as Metadata;

#[ORM\Entity(repositoryClass: UserAssetRepository::class)]
#[ORM\Table(name: 'user_asset')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Metadata\Get(security: 'is_granted("ROLE_USER")'),
        new Metadata\GetCollection(security: 'is_granted("ROLE_USER")'),
        new Metadata\Post(security: 'is_granted("ROLE_USER")'),
        new Metadata\Put(security: 'is_granted("ROLE_USER")'),
        new Metadata\Patch(security: 'is_granted("ROLE_USER")'),
        new Metadata\Delete(security: 'is_granted("ROLE_USER")'),
    ],
    normalizationContext: ['groups' => ['user_asset:read']],
    denormalizationContext: ['groups' => ['user_asset:write']]
)]
class UserAsset implements SoftDeletableTraitInterface, TimestampableTraitInterface
{
    use SoftDeletableTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['user_asset:read'])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['user_asset:read', 'user_asset:write'])]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Symbol::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['user_asset:read', 'user_asset:write'])]
    private Symbol $symbol;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    #[Groups(['user_asset:read', 'user_asset:write'])]
    private float $balance;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\NotBlank]
    #[Groups(['user_asset:read', 'user_asset:write'])]
    private string $name;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSymbol(): Symbol
    {
        return $this->symbol;
    }

    public function setSymbol(Symbol $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): self
    {
        $this->balance = $balance;

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
}
