<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class EmailUniqueValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $em) {}

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof EmailUnique) {
            throw new UnexpectedTypeException($constraint, EmailUnique::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $value]);

        $currentObject = $this->context->getObject();
        if ($existingUser && $existingUser !== $currentObject) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
