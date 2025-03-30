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
