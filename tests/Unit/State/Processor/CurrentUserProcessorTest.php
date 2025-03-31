<?php

declare(strict_types=1);

namespace App\Tests\Unit\State\Processor;

use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use App\Entity\UserAsset;
use App\State\Processor\CurrentUserProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use LogicException;

final class CurrentUserProcessorTest extends TestCase
{
    public function testSetsUserIfPossible(): void
    {
        $user = $this->createMock(User::class);
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $processor = new CurrentUserProcessor($entityManager, $security);

        $entity = $this->getMockBuilder(UserAsset::class)
            ->onlyMethods(['setUser'])
            ->getMock();

        $entity->expects(self::once())->method('setUser')->with($user)->willReturnSelf();

        $operation = $this->createMock(Operation::class);
        $result = $processor->process($entity, $operation);

        $this->assertSame($entity, $result);
    }

    public function testThrowsExceptionIfUserNotLoggedIn(): void
    {
        $this->expectException(LogicException::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $processor = new CurrentUserProcessor($entityManager, $security);

        $operation = $this->createMock(Operation::class);
        $processor->process(new UserAsset(), $operation);
    }
}
