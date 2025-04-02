<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\ActivateAccountController;
use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use App\Security\ActivationTokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ActivateAccountControllerTest extends TestCase
{
    private MockObject&UserRepositoryInterface $userRepository;
    private MockObject&EntityManagerInterface $entityManager;
    private MockObject&ActivationTokenGeneratorInterface $tokenGenerator;
    private ActivateAccountController $controller;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->tokenGenerator = $this->createMock(ActivationTokenGeneratorInterface::class);

        $this->controller = new ActivateAccountController(
            $this->userRepository,
            $this->entityManager,
            $this->tokenGenerator
        );
    }

    public function testReturnsBadRequestWhenMissingData(): void
    {
        $request = new Request();
        $response = $this->controller->__invoke($request);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('No data', (string) $response->getContent());
    }

    public function testReturnsForbiddenWhenUserNotFound(): void
    {
        $request = new Request(['email' => 'user@example.com', 'token' => 'abc']);

        $this->userRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'user@example.com'])
            ->willReturn(null);

        $response = $this->controller->__invoke($request);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertStringContainsString('Invalid token', (string) $response->getContent());
    }

    public function testReturnsForbiddenWhenTokenInvalid(): void
    {
        $user = $this->createMock(User::class);

        $this->userRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->willReturn($user);

        $this->tokenGenerator
            ->expects(self::once())
            ->method('isValid')
            ->with($user, 'invalid')
            ->willReturn(false);

        $request = new Request(['email' => 'user@example.com', 'token' => 'invalid']);

        $response = $this->controller->__invoke($request);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertStringContainsString('Invalid token', (string) $response->getContent());
    }

    public function testReturnsAlreadyActiveIfUserIsActive(): void
    {
        $user = $this->createMock(User::class);
        $user->method('isActive')->willReturn(true);

        $this->userRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->willReturn($user);

        $this->tokenGenerator
            ->expects(self::once())
            ->method('isValid')
            ->with($user, 'valid')
            ->willReturn(true);

        $request = new Request(['email' => 'user@example.com', 'token' => 'valid']);

        $response = $this->controller->__invoke($request);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('Account already active', (string) $response->getContent());
    }

    public function testActivatesAccountWhenValid(): void
    {
        $user = $this->createMock(User::class);
        $user->method('isActive')->willReturn(false);
        $user->expects(self::once())->method('setActive')->with(true);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->userRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->willReturn($user);

        $this->tokenGenerator
            ->expects(self::once())
            ->method('isValid')
            ->with($user, 'valid')
            ->willReturn(true);

        $request = new Request(['email' => 'user@example.com', 'token' => 'valid']);

        $response = $this->controller->__invoke($request);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('The account has been activated', (string) $response->getContent());
    }
}
