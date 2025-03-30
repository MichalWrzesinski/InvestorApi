<?php

declare(strict_types=1);

namespace App\Tests\Unit\Generator;

use App\Enum\SymbolType;
use App\Generator\ValidPairGenerator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ValidPairGeneratorTest extends TestCase
{
    /**
     * @dataProvider providePairCases
     */
    public function testIsValidPair(SymbolType $base, SymbolType $quote, bool $expected): void
    {
        $reflection = new ReflectionClass(ValidPairGenerator::class);
        $method = $reflection->getMethod('isValidPair');
        self::assertSame($expected, $method->invoke(null, $base, $quote));
    }

    public static function providePairCases(): iterable
    {
        yield [SymbolType::FIAT, SymbolType::FIAT, true];
        yield [SymbolType::FIAT, SymbolType::CRYPTO, true];
        yield [SymbolType::FIAT, SymbolType::STOCK, false];

        yield [SymbolType::CRYPTO, SymbolType::FIAT, true];
        yield [SymbolType::CRYPTO, SymbolType::CRYPTO, true];
        yield [SymbolType::CRYPTO, SymbolType::ETF, false];

        yield [SymbolType::STOCK, SymbolType::FIAT, true];
        yield [SymbolType::STOCK, SymbolType::CRYPTO, false];
        yield [SymbolType::STOCK, SymbolType::STOCK, false];

        yield [SymbolType::ETF, SymbolType::FIAT, true];
        yield [SymbolType::ETF, SymbolType::CRYPTO, false];
        yield [SymbolType::ETF, SymbolType::STOCK, false];
    }
}
