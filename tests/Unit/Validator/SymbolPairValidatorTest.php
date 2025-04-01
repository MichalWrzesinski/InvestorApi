<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Entity\Symbol;
use App\Enum\SymbolTypeEnum;
use App\Validator\SymbolPairValidator;
use PHPUnit\Framework\TestCase;

final class SymbolPairValidatorTest extends TestCase
{
    /**
     * @dataProvider providePairCases
     */
    public function testIsValid(SymbolTypeEnum $baseType, string $baseSymbol, SymbolTypeEnum $quoteType, string $quoteSymbol, bool $expected): void
    {
        $base = $this->createSymbol($baseType, $baseSymbol);
        $quote = $this->createSymbol($quoteType, $quoteSymbol);

        $validator = new SymbolPairValidator();

        self::assertSame($expected, $validator->isValid($base, $quote));
    }

    /** @return iterable<string, array{SymbolTypeEnum, string, SymbolTypeEnum, string, bool}> */
    public static function providePairCases(): iterable
    {
        yield 'FIAT vs FIAT' => [SymbolTypeEnum::FIAT, 'EUR', SymbolTypeEnum::FIAT, 'PLN', true];
        yield 'FIAT vs CRYPTO (invalid)' => [SymbolTypeEnum::FIAT, 'EUR', SymbolTypeEnum::CRYPTO, 'BTC', false];
        yield 'FIAT vs STOCK (invalid)' => [SymbolTypeEnum::FIAT, 'PLN', SymbolTypeEnum::STOCK, 'AAPL', false];

        yield 'CRYPTO vs USD (valid)' => [SymbolTypeEnum::CRYPTO, 'BTC', SymbolTypeEnum::FIAT, 'USD', true];
        yield 'CRYPTO vs EUR (invalid)' => [SymbolTypeEnum::CRYPTO, 'BTC', SymbolTypeEnum::FIAT, 'EUR', false];
        yield 'CRYPTO vs CRYPTO (invalid)' => [SymbolTypeEnum::CRYPTO, 'BTC', SymbolTypeEnum::CRYPTO, 'ETH', false];

        yield 'STOCK vs USD (valid)' => [SymbolTypeEnum::STOCK, 'AAPL', SymbolTypeEnum::FIAT, 'USD', true];
        yield 'STOCK vs PLN (invalid)' => [SymbolTypeEnum::STOCK, 'AAPL', SymbolTypeEnum::FIAT, 'PLN', false];
        yield 'STOCK vs STOCK (invalid)' => [SymbolTypeEnum::STOCK, 'AAPL', SymbolTypeEnum::STOCK, 'MSFT', false];

        yield 'ETF vs USD (valid)' => [SymbolTypeEnum::ETF, 'VOO', SymbolTypeEnum::FIAT, 'USD', true];
        yield 'ETF vs EUR (invalid)' => [SymbolTypeEnum::ETF, 'VOO', SymbolTypeEnum::FIAT, 'EUR', false];
        yield 'ETF vs CRYPTO (invalid)' => [SymbolTypeEnum::ETF, 'VOO', SymbolTypeEnum::CRYPTO, 'BTC', false];
    }

    private function createSymbol(SymbolTypeEnum $type, string $symbol): Symbol
    {
        $mock = $this->createMock(Symbol::class);
        $mock->method('getType')->willReturn($type);
        $mock->method('getSymbol')->willReturn($symbol);

        return $mock;
    }
}
