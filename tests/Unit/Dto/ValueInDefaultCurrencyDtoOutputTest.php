<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\ValueInDefaultCurrencyDtoOutput;
use PHPUnit\Framework\TestCase;

final class ValueInDefaultCurrencyDtoOutputTest extends TestCase
{
    public function testDtoStoresGivenData(): void
    {
        $dto = new ValueInDefaultCurrencyDtoOutput(1234.56, 'USD');

        $this->assertSame(1234.56, $dto->value);
        $this->assertSame('USD', $dto->quote);
    }

    public function testDtoAcceptsZeroAndOtherCurrency(): void
    {
        $dto = new ValueInDefaultCurrencyDtoOutput(0.0, 'PLN');

        $this->assertSame(0.0, $dto->value);
        $this->assertSame('PLN', $dto->quote);
    }
}
