<?php

declare(strict_types=1);

namespace App\Tests\Unit\State\Processor\DataFixture;

use App\Entity\User;
use App\State\Processor\DataFixture\UserPasswordProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use stdClass;

final class UserPasswordProcessorTest extends TestCase
{
    private UserPasswordHasherInterface&MockObject $hasher;
    private UserPasswordProcessor $processor;

    protected function setUp(): void
    {
        $this->hasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->processor = new UserPasswordProcessor($this->hasher);
    }

    public function testPreProcessHashesPasswordForUser(): void
    {
        $user = new User();
        $user->setPassword('plain');

        $this->hasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'plain')
            ->willReturn('hashed');

        $this->processor->preProcess('user_1', $user);

        $this->assertSame('hashed', $user->getPassword());
    }

    public function testPreProcessSkipsNonUserObjects(): void
    {
        $object = new stdClass();

        $this->hasher->expects($this->never())->method('hashPassword');
        $this->processor->preProcess('other_id', $object);
    }
}
