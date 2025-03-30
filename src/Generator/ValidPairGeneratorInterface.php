<?php

namespace App\Generator;

interface ValidPairGeneratorInterface
{
    public function generate(): iterable;
}
