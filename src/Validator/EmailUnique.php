<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class EmailUnique extends Constraint
{
    public string $message = 'This email is already in use.';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
