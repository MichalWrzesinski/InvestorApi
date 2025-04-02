<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\SoftDeletableTrait;
use App\Entity\Trait\SoftDeletableTraitInterface;
use App\Entity\Trait\TimestampableTrait;
use App\Entity\Trait\TimestampableTraitInterface;
use App\Repository\ExchangeRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
#[ORM\Table(name: 'exchange_rate')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Metadata\Get(security: 'is_granted("ROLE_USER")'),
        new Metadata\GetCollection(security: 'is_granted("ROLE_USER")'),
        new Metadata\Post(security: 'is_granted("ROLE_ADMIN")'),
        new Metadata\Put(security: 'is_granted("ROLE_ADMIN")'),
        new Metadata\Patch(security: 'is_granted("ROLE_ADMIN")'),
        new Metadata\Delete(security: 'is_granted("ROLE_ADMIN")'),
    ],
    normalizationContext: ['groups' => ['exchange_rate:read']],
    denormalizationContext: ['groups' => ['exchange_rate:write']]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'base' => 'exact',
    'quote' => 'exact',
])]
#[ApiFilter(RangeFilter::class, properties: [
    'price',
])]
#[ApiFilter(DateFilter::class, properties: [
    'createdAt',
    'updatedAt',
    'deletedAt',
])]
#[ApiFilter(OrderFilter::class, properties: [
    'base',
    'quote',
    'price',
])]
class ExchangeRate implements SoftDeletableTraitInterface, TimestampableTraitInterface
{
    use TimestampableTrait;
    use SoftDeletableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['exchange_rate:read'])]
    private ?Uuid $id = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: Symbol::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(readableLink: true)]
    #[Groups(['exchange_rate:read', 'exchange_rate:write', 'symbol:read'])]
    private Symbol $base;

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: Symbol::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(readableLink: true)]
    #[Groups(['exchange_rate:read', 'exchange_rate:write', 'symbol:read'])]
    private Symbol $quote;

    #[Assert\NotBlank]
    #[Assert\Positive]
    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(['exchange_rate:read', 'exchange_rate:write'])]
    private float $price;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getBase(): Symbol
    {
        return $this->base;
    }

    public function setBase(Symbol $base): self
    {
        $this->base = $base;

        return $this;
    }

    public function getQuote(): Symbol
    {
        return $this->quote;
    }

    public function setQuote(Symbol $quote): self
    {
        $this->quote = $quote;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }
}
