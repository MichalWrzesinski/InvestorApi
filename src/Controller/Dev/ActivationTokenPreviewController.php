<?php

declare(strict_types=1);

namespace App\Controller\Dev;

use App\Repository\UserRepositoryInterface;
use App\Security\ActivationTokenGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final readonly class ActivationTokenPreviewController
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ActivationTokenGeneratorInterface $tokenGenerator,
    ) {
    }

    #[Route('/api/dev/activation-token', name: 'dev_activation_token', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $email = $request->query->get('email');
        if (!$email) {
            return new JsonResponse(['error' => 'Missing email'], 400);
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        return new JsonResponse([
            'token' => $this->tokenGenerator->generate($user),
            'email' => $email,
        ]);
    }
}
