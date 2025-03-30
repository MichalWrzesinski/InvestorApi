<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Trait\TimestampableTrait;
use App\Entity\Trait\SoftDeletableTrait;
use App\Repository\ExchangeRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata as Metadata;

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
class ExchangeRate
{
    use TimestampableTrait;
    use SoftDeletableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['exchange_rate:read'])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: Symbol::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(readableLink: true)]
    #[Groups(['exchange_rate:read', 'exchange_rate:write', 'symbol:read'])]
    private Symbol $base;

    #[ORM\ManyToOne(targetEntity: Symbol::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(readableLink: true)]
    #[Groups(['exchange_rate:read', 'exchange_rate:write', 'symbol:read'])]
    private Symbol $quote;

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
