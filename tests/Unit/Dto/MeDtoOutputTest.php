<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\MeDtoOutput;
use App\Dto\ValueInDefaultCurrencyDtoOutput;
use PHPUnit\Framework\TestCase;

final class MeDtoOutputTest extends TestCase
{
    public function testDtoStoresGivenData(): void
    {
        $email = 'test@example.com';
        $defaultQuoteSymbol = ['symbol' => 'USD', 'name' => 'US Dollar'];

        $assets = [
            ['id' => 1, 'name' => 'Konto walutowe', 'balance' => 1000],
            ['id' => 2, 'name' => 'Giełda', 'balance' => 5000],
        ];

        $totalBalance = new ValueInDefaultCurrencyDtoOutput(6000.0, 'USD');

        $dto = new MeDtoOutput(
            email: $email,
            defaultQuoteSymbol: $defaultQuoteSymbol,
            assets: $assets,
            totalBalance: $totalBalance
        );

        $this->assertSame($email, $dto->email);
        $this->assertSame($defaultQuoteSymbol, $dto->defaultQuoteSymbol);
        $this->assertSame($assets, $dto->assets);
        $this->assertSame($totalBalance, $dto->totalBalance);
    }

    public function testDtoAcceptsNullAsDefaultQuoteSymbol(): void
    {
        $dto = new MeDtoOutput(
            email: 'test@example.com',
            defaultQuoteSymbol: null,
            assets: [],
            totalBalance: new ValueInDefaultCurrencyDtoOutput(0.0, 'USD')
        );

        $this->assertNull($dto->defaultQuoteSymbol);
        $this->assertSame([], $dto->assets);
        $this->assertSame('test@example.com', $dto->email);
        $this->assertSame(0.0, $dto->totalBalance->value);
    }
}
