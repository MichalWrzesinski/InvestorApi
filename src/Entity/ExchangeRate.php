<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\TimestampableTrait;
use App\Entity\Trait\SoftDeletableTrait;
use App\Repository\ExchangeRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
#[ORM\Table(name: 'exchange_rate')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
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
    #[Groups(['exchange_rate:read', 'exchange_rate:write'])]
    private Symbol $base;

    #[ORM\ManyToOne(targetEntity: Symbol::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['exchange_rate:read', 'exchange_rate:write'])]
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
