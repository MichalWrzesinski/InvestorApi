<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\LatestPriceDtoOutput;
use PHPUnit\Framework\TestCase;

final class LatestPriceDtoOutputTest extends TestCase
{
    public function testCanBeCreatedWithValidData(): void
    {
        $value = 123.45;
        $quote = 'USD';

        $dto = new LatestPriceDtoOutput($value, $quote);

        $this->assertSame($value, $dto->value);
        $this->assertSame($quote, $dto->quote);
    }
}
