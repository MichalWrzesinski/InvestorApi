<?php

namespace App\Message;

class UpdateRatesMessage implements MessageInterface
{
    public function __construct(
        public readonly string $processor,
        public readonly array $pairs,
    ) {}
}
