<?php

declare(strict_types=1);

namespace App\Tests\Unit\Generator;

use App\Entity\Symbol;
use App\Enum\SymbolTypeEnum;
use App\Generator\ValidSymbolPairGenerator;
use App\Repository\SymbolRepositoryInterface;
use App\Validator\SymbolPairValidatorInterface;
use PHPUnit\Framework\TestCase;

final class ValidSymbolPairGeneratorTest extends TestCase
{
    /**
     * @param array<array{0: string, 1: SymbolTypeEnum}> $symbolDefs
     * @param array<array{0: string, 1: string}>         $expectedPairs
     *
     * @dataProvider provideSymbolPairs
     */
    public function testGenerateYieldsExpectedPairs(array $symbolDefs, array $expectedPairs): void
    {
        $symbols = [];
        foreach ($symbolDefs as [$code, $type]) {
            $symbols[] = $this->createSymbolMock($code, $type);
        }

        $symbolRepository = $this->createMock(SymbolRepositoryInterface::class);
        $symbolRepository->method('findAll')->willReturn($symbols);

        $validator = $this->createMock(SymbolPairValidatorInterface::class);
        $validator->method('isValid')
            ->willReturnCallback(function (Symbol $base, Symbol $quote): bool {
                $baseType = $base->getType();
                $quoteType = $quote->getType();
                $quoteSymbol = $quote->getSymbol();

                if (SymbolTypeEnum::FIAT === $baseType && SymbolTypeEnum::CRYPTO === $quoteType) {
                    return true;
                }

                if (SymbolTypeEnum::STOCK === $baseType && SymbolTypeEnum::FIAT === $quoteType && 'USD' === $quoteSymbol) {
                    return true;
                }

                return false;
            });

        $generator = new ValidSymbolPairGenerator($symbolRepository, $validator);
        $actualPairs = iterator_to_array($generator->generate());

        $this->assertCount(count($expectedPairs), $actualPairs);

        foreach ($expectedPairs as $index => [$expectedBase, $expectedQuote]) {
            $this->assertSame($expectedBase, $actualPairs[$index]['base']->getSymbol());
            $this->assertSame($expectedQuote, $actualPairs[$index]['quote']->getSymbol());
        }
    }

    /**
     * @return iterable<array{
     *     0: array<array{0: string, 1: SymbolTypeEnum}>,
     *     1: array<array{0: string, 1: string}>
     * }>
     */
    public static function provideSymbolPairs(): iterable
    {
        yield [
            [
                ['USD', SymbolTypeEnum::FIAT],
                ['BTC', SymbolTypeEnum::CRYPTO],
                ['AAPL', SymbolTypeEnum::STOCK],
            ],
            [
                ['USD', 'BTC'],
                ['AAPL', 'USD'],
            ],
        ];
    }

    private function createSymbolMock(string $code, SymbolTypeEnum $type): Symbol
    {
        $symbol = $this->createMock(Symbol::class);
        $symbol->method('getType')->willReturn($type);
        $symbol->method('getSymbol')->willReturn($code);

        return $symbol;
    }
}
