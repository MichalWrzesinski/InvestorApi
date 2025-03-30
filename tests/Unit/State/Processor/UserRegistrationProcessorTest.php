<?php

declare(strict_types=1);

namespace App\Tests\Unit\State\Processor;

use ApiPlatform\Metadata\Operation;
use App\Dto\UserRegistrationInput;
use App\Entity\User;
use App\State\Processor\UserRegistrationProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserRegistrationProcessorTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private UserPasswordHasherInterface&MockObject $hasher;

    private UserRegistrationProcessor $processor;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->hasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->processor = new UserRegistrationProcessor(
            $this->entityManager,
            $this->hasher
        );
    }

    public function testProcessCreatesAndPersistsUser(): void
    {
        $input = new UserRegistrationInput();
        $input->email = 'test@example.com';
        $input->password = 'plainPassword';

        $operation = $this->createMock(Operation::class);

        $this->hasher->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(User::class), 'plainPassword')
            ->willReturn('hashedPassword');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (User $user) use ($input) {
                return
                    $user->getEmail() === $input->email &&
                    $user->getPassword() === 'hashedPassword' &&
                    $user->isActive() === true &&
                    $user->getRoles() === ['ROLE_USER'];
            }));

        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->processor->process($input, $operation);

        $this->assertInstanceOf(User::class, $result);
    }
}
