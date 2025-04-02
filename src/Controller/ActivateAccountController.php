<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepositoryInterface;
use App\Security\ActivationTokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final readonly class ActivateAccountController
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EntityManagerInterface $entityManager,
        private ActivationTokenGeneratorInterface $tokenGenerator,
    ) {
    }

    #[Route('/api/activate', name: 'account_activate', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $email = $request->query->get('email');
        $token = $request->query->get('token');

        if (!$email || !$token) {
            return new JsonResponse(['error' => 'No data'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user || !$this->tokenGenerator->isValid($user, $token)) {
            return new JsonResponse(['error' => 'Invalid token'], Response::HTTP_FORBIDDEN);
        }

        if ($user->isActive()) {
            return new JsonResponse(['message' => 'Account already active']);
        }

        $user->setActive(true);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'The account has been activated']);
    }
}
