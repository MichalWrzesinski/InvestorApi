<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
#[ORM\Table(name: 'exchange_rate_latest')]
#[ApiResource(
    operations: [
        new Metadata\Get(security: 'is_granted("ROLE_USER")'),
        new Metadata\GetCollection(security: 'is_granted("ROLE_USER")'),
        new Metadata\Post(security: 'is_granted("ROLE_ADMIN")'),
        new Metadata\Put(security: 'is_granted("ROLE_ADMIN")'),
        new Metadata\Patch(security: 'is_granted("ROLE_ADMIN")'),
        new Metadata\Delete(security: 'is_granted("ROLE_ADMIN")'),
    ]
)]
class ExchangeRateLatest
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 10)]
    private string $baseSymbol;

    #[ORM\Column(type: Types::STRING, length: 10)]
    private string $quoteSymbol;

    #[ORM\Column(type: Types::FLOAT)]
    private float $price;

    public function getId(): string
    {
        return $this->id;
    }

    public function getBase(): string
    {
        return $this->baseSymbol;
    }

    public function getQuote(): string
    {
        return $this->quoteSymbol;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}
