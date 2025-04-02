<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Dev;

use App\Entity\User;
use App\Security\ActivationTokenGenerator;
use App\Tests\Functional\FunctionalTestCase;

final class ActivationTokenPreviewControllerTest extends FunctionalTestCase
{
    public function testPreviewTokenEndpointReturnsValidToken(): void
    {
        $container = $this->getTestContainer();

        /** @var User $user */
        $user = $container
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'test_admin@example.com']);

        self::assertInstanceOf(User::class, $user);

        /** @var ActivationTokenGenerator $tokenGenerator */
        $tokenGenerator = $container->get(ActivationTokenGenerator::class);

        $expectedToken = $tokenGenerator->generate($user);

        $client = $this->requestJson('GET', sprintf('/api/dev/activation-token?email=%s', urlencode($user->getEmail())));

        $response = $client->getResponse();
        $content = $response->getContent();
        self::assertIsString($content);

        $data = json_decode($content, true);
        self::assertIsArray($data);

        self::assertSame(200, $response->getStatusCode());
        self::assertArrayHasKey('token', $data);
        self::assertArrayHasKey('email', $data);
        self::assertSame($user->getEmail(), $data['email']);
        self::assertSame($expectedToken, $data['token']);
    }

    public function testPreviewTokenEndpointFailsWithInvalidEmail(): void
    {
        $client = $this->requestJson('GET', '/api/dev/activation-token?email=nonexistent@example.com');
        $response = $client->getResponse();
        $content = $response->getContent();
        self::assertIsString($content);

        $data = json_decode($content, true);
        self::assertIsArray($data);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame(['error' => 'User not found'], $data);
    }

    public function testPreviewTokenEndpointFailsWithMissingEmail(): void
    {
        $client = $this->requestJson('GET', '/api/dev/activation-token');
        $response = $client->getResponse();
        $content = $response->getContent();
        self::assertIsString($content);

        $data = json_decode($content, true);
        self::assertIsArray($data);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(['error' => 'Missing email'], $data);
    }
}
