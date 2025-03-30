<?php

declare(strict_types=1);

namespace App\Tests\Unit\Generator;

use App\Entity\Symbol;
use App\Enum\SymbolType;
use App\Generator\ValidPairGenerator;
use App\Repository\SymbolRepository;
use App\Validator\SymbolPairValidatorInterface;
use PHPUnit\Framework\TestCase;

final class ValidPairGeneratorTest extends TestCase
{
    /**
     * @dataProvider provideSymbolPairs
     */
    public function testGenerateYieldsExpectedPairs(array $symbolDefs, array $expectedPairs): void
    {
        $symbols = [];
        foreach ($symbolDefs as [$code, $type]) {
            $symbols[] = $this->createSymbolMock($code, $type);
        }

        $symbolRepository = $this->createMock(SymbolRepository::class);
        $symbolRepository->method('findAll')->willReturn($symbols);

        $validator = $this->createMock(SymbolPairValidatorInterface::class);
        $validator->method('isValid')
            ->willReturnCallback(fn(SymbolType $base, SymbolType $quote) =>
                match ([$base, $quote]) {
                    [SymbolType::FIAT, SymbolType::CRYPTO] => true,
                    [SymbolType::STOCK, SymbolType::FIAT] => true,
                    default => false,
                });

        $generator = new ValidPairGenerator($symbolRepository, $validator);
        $actualPairs = iterator_to_array($generator->generate());

        $this->assertCount(count($expectedPairs), $actualPairs);

        foreach ($expectedPairs as $index => [$expectedBase, $expectedQuote]) {
            $this->assertSame($expectedBase, $actualPairs[$index]['base']->getSymbol());
            $this->assertSame($expectedQuote, $actualPairs[$index]['quote']->getSymbol());
        }
    }

    public static function provideSymbolPairs(): iterable
    {
        yield [
            [
                ['USD', SymbolType::FIAT],
                ['BTC', SymbolType::CRYPTO],
                ['AAPL', SymbolType::STOCK],
            ],
            [
                ['USD', 'BTC'],
                ['AAPL', 'USD'],
            ],
        ];
    }

    private function createSymbolMock(string $code, SymbolType $type): Symbol
    {
        $symbol = $this->createMock(Symbol::class);
        $symbol->method('getType')->willReturn($type);
        $symbol->method('getSymbol')->willReturn($code);

        return $symbol;
    }
}
