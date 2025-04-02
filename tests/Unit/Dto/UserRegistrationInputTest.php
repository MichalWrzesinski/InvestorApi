<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\UserRegistrationInput;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserRegistrationInputTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidInput(): void
    {
        $dto = new UserRegistrationInput();
        $dto->email = 'test@example.com';
        $dto->password = 'securePassword123';

        $this->assertCount(0, $this->validator->validate($dto));
    }

    public function testInvalidEmailAndBlankPassword(): void
    {
        $dto = new UserRegistrationInput();
        $dto->email = 'invalid-email';
        $dto->password = '';

        /** @var ConstraintViolationListInterface $violations */
        $violations = $this->validator->validate($dto);

        $this->assertGreaterThanOrEqual(2, count($violations));

        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = $violation->getMessage();
        }

        $this->assertContains('This value is not a valid email address.', $messages);
        $this->assertContains('This value should not be blank.', $messages);
    }

    public function testPasswordTooShort(): void
    {
        $dto = new UserRegistrationInput();
        $dto->email = 'test@example.com';
        $dto->password = 'short';

        $violations = $this->validator->validate($dto);
        $this->assertCount(1, $violations);

        $this->assertSame(
            'This value is too short. It should have 8 characters or more.',
            $violations->get(0)->getMessage()
        );
    }
}
