<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\DataProcessor;
use App\Enum\SymbolType;
use App\Repository\SymbolRepository;
use App\Entity\Trait\SoftDeletableTrait;
use App\Entity\Trait\TimestampableTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SymbolRepository::class)]
#[ORM\Table(name: 'symbol')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['symbol:read']],
    denormalizationContext: ['groups' => ['symbol:write']]
)]
class Symbol
{
    use TimestampableTrait;
    use SoftDeletableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['symbol:read'])]
    private ?Uuid $id = null;

    #[ORM\Column(type: Types::STRING, length: 15, unique: true)]
    #[Groups(['symbol:read', 'symbol:write'])]
    private string $symbol;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Groups(['symbol:read', 'symbol:write'])]
    private string $name;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups(['symbol:read', 'symbol:write'])]
    private ?float $price = null;

    #[ORM\Column(enumType: SymbolType::class)]
    #[Groups(['symbol:read', 'symbol:write'])]
    private SymbolType $type;

    #[ORM\Column(enumType: DataProcessor::class)]
    #[Groups(['symbol:read', 'symbol:write'])]
    private DataProcessor $processor;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getType(): SymbolType
    {
        return $this->type;
    }

    public function setType(SymbolType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getProcessor(): DataProcessor
    {
        return $this->processor;
    }

    public function setProcessor(DataProcessor $processor): self
    {
        $this->processor = $processor;

        return $this;
    }
}
