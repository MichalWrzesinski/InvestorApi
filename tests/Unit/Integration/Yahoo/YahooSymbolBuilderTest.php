<?php

declare(strict_types=1);

namespace App\Tests\Unit\Integration\Yahoo;

use App\Enum\SymbolTypeEnum;
use App\Integration\Yahoo\YahooSymbolBuilder;
use PHPUnit\Framework\TestCase;

final class YahooSymbolBuilderTest extends TestCase
{
    private YahooSymbolBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new YahooSymbolBuilder();
    }

    public function testBuildFiatSymbol(): void
    {
        $symbol = $this->builder->build(SymbolTypeEnum::FIAT, 'usd', 'pln');

        $this->assertSame('USDPLN=X', $symbol);
    }

    public function testBuildCryptoSymbol(): void
    {
        $symbol = $this->builder->build(SymbolTypeEnum::CRYPTO, 'btc', 'usdt');

        $this->assertSame('BTC-USDT', $symbol);
    }

    public function testBuildStockSymbol(): void
    {
        $symbol = $this->builder->build(SymbolTypeEnum::STOCK, 'AAPL', null);

        $this->assertSame('AAPL', $symbol);
    }

    public function testBuildEtfSymbol(): void
    {
        $symbol = $this->builder->build(SymbolTypeEnum::ETF, 'voo', null);

        $this->assertSame('VOO', $symbol);
    }
}
