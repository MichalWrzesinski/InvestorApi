<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Security\ActivationTokenGenerator;
use PHPUnit\Framework\TestCase;

final class ActivationTokenGeneratorTest extends TestCase
{
    private ActivationTokenGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new ActivationTokenGenerator('my-secret-key');
    }

    public function testGenerateReturnsValidToken(): void
    {
        $user = $this->createUser('test@example.com', new \DateTimeImmutable('2023-01-01 12:00:00'));
        $token = $this->generator->generate($user);

        $createdAt = $user->getCreatedAt();
        self::assertInstanceOf(\DateTimeImmutable::class, $createdAt);

        $this->assertSame($token, hash_hmac(
            'sha256',
            sprintf('test@example.com|%d', $createdAt->getTimestamp()),
            'my-secret-key'
        ));
    }

    public function testIsValidReturnsTrueForCorrectToken(): void
    {
        $user = $this->createUser('foo@bar.com', new \DateTimeImmutable('2024-02-02 10:00:00'));
        $token = $this->generator->generate($user);

        $this->assertTrue($this->generator->isValid($user, $token));
    }

    public function testIsValidReturnsFalseForIncorrectToken(): void
    {
        $user = $this->createUser('foo@bar.com', new \DateTimeImmutable('2024-02-02 10:00:00'));

        $this->assertFalse($this->generator->isValid($user, 'invalid-token'));
    }

    public function testGenerateThrowsExceptionWhenCreatedAtIsNull(): void
    {
        $user = $this->createUser('test@example.com', null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User has no creation date set');

        $this->generator->generate($user);
    }

    private function createUser(string $email, ?\DateTimeImmutable $createdAt): User
    {
        return new class($email, $createdAt) extends User {
            public function __construct(
                private readonly string $email,
                private readonly ?\DateTimeImmutable $createdAt,
            ) {
            }

            public function getEmail(): string
            {
                return $this->email;
            }

            public function getCreatedAt(): ?\DateTimeImmutable
            {
                return $this->createdAt;
            }
        };
    }
}
