<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Enum\SymbolTypeEnum;
use App\Validator\SymbolPairValidator;
use PHPUnit\Framework\TestCase;

final class SymbolPairValidatorTest extends TestCase
{
    /**
     * @dataProvider providePairCases
     */
    public function testIsValid(SymbolTypeEnum $base, SymbolTypeEnum $quote, bool $expected): void
    {

        self::assertSame(
            $expected,
            (new SymbolPairValidator())->isValid($base, $quote)
        );
    }

    /** @return iterable<string, array{SymbolTypeEnum, SymbolTypeEnum, bool}> */
    public static function providePairCases(): iterable
    {
        yield 'FIAT vs FIAT' => [SymbolTypeEnum::FIAT, SymbolTypeEnum::FIAT, true];
        yield 'FIAT vs CRYPTO (invalid)' => [SymbolTypeEnum::FIAT, SymbolTypeEnum::CRYPTO, true];
        yield 'FIAT vs STOCK (invalid)' => [SymbolTypeEnum::FIAT, SymbolTypeEnum::STOCK, false];

        yield 'CRYPTO vs FIAT' => [SymbolTypeEnum::CRYPTO, SymbolTypeEnum::FIAT, true];
        yield 'CRYPTO vs CRYPTO' => [SymbolTypeEnum::CRYPTO, SymbolTypeEnum::CRYPTO, true];
        yield 'CRYPTO vs ETF (invalid)' => [SymbolTypeEnum::CRYPTO, SymbolTypeEnum::ETF, false];

        yield 'STOCK vs FIAT' => [SymbolTypeEnum::STOCK, SymbolTypeEnum::FIAT, true];
        yield 'STOCK vs CRYPTO (invalid)' => [SymbolTypeEnum::STOCK, SymbolTypeEnum::CRYPTO, false];
        yield 'STOCK vs FIAT (invalid)' => [SymbolTypeEnum::STOCK, SymbolTypeEnum::STOCK, false];

        yield 'ETF vs FIAT' => [SymbolTypeEnum::ETF, SymbolTypeEnum::FIAT, true];
        yield 'ETF vs FIAT (invalid)' => [SymbolTypeEnum::ETF, SymbolTypeEnum::CRYPTO, false];
        yield 'ETF vs STOCK (invalid)' => [SymbolTypeEnum::ETF, SymbolTypeEnum::STOCK, false];
    }
}
