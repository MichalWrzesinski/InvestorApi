<?php

declare(strict_types=1);

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Validator\EmailUnique;
use Symfony\Component\Validator\Constraints as Assert;
use App\State\Processor\UserRegistrationProcessor;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/register',
            security: 'is_granted("PUBLIC_ACCESS")',
            input: UserRegistrationInput::class,
            output: false,
            processor: UserRegistrationProcessor::class
        )
    ],
    normalizationContext: ['groups' => ['registration:read']],
    denormalizationContext: ['groups' => ['registration:write']]
)]
final class UserRegistrationInput
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[EmailUnique]
    #[Groups(['registration:write'])]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    #[Groups(['registration:write'])]
    public string $password;
}
