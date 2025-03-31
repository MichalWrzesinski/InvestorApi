<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Enum\SymbolType;
use App\Validator\SymbolPairValidator;
use PHPUnit\Framework\TestCase;

final class SymbolPairValidatorTest extends TestCase
{
    /**
     * @dataProvider providePairCases
     */
    public function testIsValid(SymbolType $base, SymbolType $quote, bool $expected): void
    {

        self::assertSame(
            $expected,
            (new SymbolPairValidator())->isValid($base, $quote)
        );
    }

    /** @return iterable<string, array{SymbolType, SymbolType, bool}> */
    public static function providePairCases(): iterable
    {
        yield 'FIAT vs FIAT' => [SymbolType::FIAT, SymbolType::FIAT, true];
        yield 'FIAT vs CRYPTO (invalid)' => [SymbolType::FIAT, SymbolType::CRYPTO, true];
        yield 'FIAT vs STOCK (invalid)' => [SymbolType::FIAT, SymbolType::STOCK, false];

        yield 'CRYPTO vs FIAT' => [SymbolType::CRYPTO, SymbolType::FIAT, true];
        yield 'CRYPTO vs CRYPTO' => [SymbolType::CRYPTO, SymbolType::CRYPTO, true];
        yield 'CRYPTO vs ETF (invalid)' => [SymbolType::CRYPTO, SymbolType::ETF, false];

        yield 'STOCK vs FIAT' => [SymbolType::STOCK, SymbolType::FIAT, true];
        yield 'STOCK vs CRYPTO (invalid)' => [SymbolType::STOCK, SymbolType::CRYPTO, false];
        yield 'STOCK vs FIAT (invalid)' => [SymbolType::STOCK, SymbolType::STOCK, false];

        yield 'ETF vs FIAT' => [SymbolType::ETF, SymbolType::FIAT, true];
        yield 'ETF vs FIAT (invalid)' => [SymbolType::ETF, SymbolType::CRYPTO, false];
        yield 'ETF vs STOCK (invalid)' => [SymbolType::ETF, SymbolType::STOCK, false];
    }
}
