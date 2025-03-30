<?php

declare(strict_types=1);

namespace App\Generator;

use App\Entity\Symbol;
use App\Enum\SymbolType;
use App\Repository\SymbolRepository;

class ValidPairGenerator
{
    public function __construct(
        private readonly SymbolRepository $symbolRepository,
    ) {}

    /**
     * @return iterable<array{base: Symbol, quote: Symbol}>
     */
    public function generate(): iterable
    {
        $symbols = $this->symbolRepository->findAll();

        foreach ($symbols as $base) {
            foreach ($symbols as $quote) {
                if ($base === $quote) {
                    continue;
                }

                if (self::isValidPair($base->getType(), $quote->getType())) {
                    yield ['base' => $base, 'quote' => $quote];
                }
            }
        }
    }

    private static function isValidPair(SymbolType $baseType, SymbolType $quoteType): bool
    {
        return match ($baseType) {
            SymbolType::FIAT => in_array($quoteType, [SymbolType::FIAT, SymbolType::CRYPTO], true),
            SymbolType::CRYPTO => in_array($quoteType, [SymbolType::FIAT, SymbolType::CRYPTO], true),
            SymbolType::STOCK, SymbolType::ETF => $quoteType === SymbolType::FIAT,
        };
    }
}
