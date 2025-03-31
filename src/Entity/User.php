<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\SoftDeletableTraitInterface;
use App\Entity\Trait\TimestampableTraitInterface;
use App\Repository\UserRepository;
use App\Entity\Trait\SoftDeletableTrait;
use App\Entity\Trait\TimestampableTrait;
use App\Validator\EmailUnique;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata as Metadata;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Metadata\Get(security: 'is_granted("ROLE_USER")'),
        new Metadata\GetCollection(security: 'is_granted("ROLE_ADMIN")'),
        new Metadata\Post(security: 'is_granted("ROLE_ADMIN")'),
        new Metadata\Put(security: 'is_granted("ROLE_ADMIN")'),
        new Metadata\Patch(security: 'is_granted("ROLE_ADMIN")'),
        new Metadata\Delete(security: 'is_granted("ROLE_ADMIN")'),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, SoftDeletableTraitInterface, TimestampableTraitInterface
{
    use TimestampableTrait;
    use SoftDeletableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[Assert\NotNull]
    #[EmailUnique]
    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write'])]
    private string $email;

    #[Assert\NotNull]
    #[ORM\Column]
    private string $password;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?bool $active = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function isActive(): bool
    {
        return true === $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {}
}
