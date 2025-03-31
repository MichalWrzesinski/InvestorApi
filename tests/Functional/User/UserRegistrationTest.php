<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use App\Entity\User;
use App\Tests\Functional\FunctionalTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Generator;

final class UserRegistrationTest extends FunctionalTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->getTestContainer()->get(EntityManagerInterface::class);
        $this->entityManager->createQuery('DELETE FROM App\Entity\User u')->execute();
    }

    public function testSuccessfulRegistration(): void
    {
        $payload = [
            'email' => 'test@example.com',
            'password' => 'StrongPass123',
        ];

         $this->requestJson('POST', '/api/register', $payload);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $payload['email']]);

        $this->assertNotNull($user);
        $this->assertSame($payload['email'], $user->getEmail());
        $this->assertSame(['ROLE_USER'], $user->getRoles());
        $this->assertTrue($user->isActive());
        $this->assertNotSame($payload['password'], $user->getPassword(), 'Password should be hashed.');
    }

    /**
     * @param array<string, string> $payload
     * @param string $expectedField
     *
     * @dataProvider provideInvalidRegistrationData
     */
    public function testRegistrationValidationErrors(array $payload, string $expectedField): void
    {
        if ($expectedField === 'email_duplicate') {
            $this->requestJson('POST', '/api/register', [
                'email' => $payload['email'],
                'password' => 'SomePass123',
            ]);
        }

        $client = $this->requestJson('POST', '/api/register', $payload);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $expectedCheck = $expectedField === 'email_duplicate' ? 'email' : $expectedField;
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString($expectedCheck, $content);
    }

    /** @return Generator<string, array{0: array<string, string>, 1: string}> */
    public static function provideInvalidRegistrationData(): Generator
    {
        yield 'missing password' => [
            ['email' => 'user1@example.com'],
            'password',
        ];

        yield 'missing email' => [
            ['password' => 'Password123'],
            'email',
        ];

        yield 'invalid email format' => [
            ['email' => 'invalid-email', 'password' => 'Password123'],
            'email',
        ];

        yield 'duplicate email' => [
            ['email' => 'existing@example.com', 'password' => 'Password123'],
            'email_duplicate',
        ];
    }
}
