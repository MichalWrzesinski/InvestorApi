<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Entity\User;
use App\Enum\SymbolTypeEnum;
use App\Validator\EmailUnique;
use App\Validator\EmailUniqueValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use stdClass;

class EmailUniqueValidatorTest extends TestCase
{
    private EmailUniqueValidator $validator;

    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface&MockObject $entityManager;

    /** @var EntityRepository<User>&MockObject */
    private EntityRepository&MockObject $repository;

    /** @var ExecutionContextInterface&MockObject */
    private ExecutionContextInterface&MockObject $context;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EntityRepository::class);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->repository);

        $this->validator = new EmailUniqueValidator($this->entityManager);

        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    /**
     * @dataProvider provideEmails
     */
    public function testEmailUniqueness(
        mixed $value,
        ?User $existingUser,
        mixed $currentObject,
        bool $expectViolation
    ): void {
        if ($existingUser !== null) {
            $this->repository
                ->expects($this->once())
                ->method('findOneBy')
                ->with(['email' => $value])
                ->willReturn($existingUser);
        } else {
            $this->repository
                ->expects($value === null || $value === '' ? $this->never() : $this->once())
                ->method('findOneBy')
                ->with(['email' => $value])
                ->willReturn(null);
        }

        $this->context
            ->method('getObject')
            ->willReturn($currentObject);

        if ($expectViolation) {
            $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
            $this->context
                ->expects($this->once())
                ->method('buildViolation')
                ->with('This email is already in use.')
                ->willReturn($violationBuilder);
            $violationBuilder
                ->expects($this->once())
                ->method('addViolation');
        } else {
            $this->context
                ->expects($this->never())
                ->method('buildViolation');
        }

        $this->validator->validate($value, new EmailUnique());
    }

    /**
     * @return iterable<string, array{
     *     value: mixed,
     *     existingUser: ?User,
     *     currentObject: mixed,
     *     expectViolation: bool
     * }>
     */
    public static function provideEmails(): iterable
    {
        $existingUser = new User();

        yield 'null email' => [
            'value' => null,
            'existingUser' => null,
            'currentObject' => null,
            'expectViolation' => false,
        ];

        yield 'empty string' => [
            'value' => '',
            'existingUser' => null,
            'currentObject' => null,
            'expectViolation' => false,
        ];

        yield 'new email' => [
            'value' => 'nowy@user.pl',
            'existingUser' => null,
            'currentObject' => null,
            'expectViolation' => false,
        ];

        yield 'duplicate email (different object)' => [
            'value' => 'istnieje@user.pl',
            'existingUser' => $existingUser,
            'currentObject' => new stdClass(),
            'expectViolation' => true,
        ];

        yield 'same user (no violation)' => [
            'value' => 'ten.sam@user.pl',
            'existingUser' => $existingUser,
            'currentObject' => $existingUser,
            'expectViolation' => false,
        ];
    }
}
