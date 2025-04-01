<?php

declare(strict_types=1);

namespace App\Generator;

use App\Entity\Symbol;
use App\Repository\SymbolRepositoryInterface;
use App\Validator\SymbolPairValidatorInterface;

final readonly class ValidSymbolPairGenerator implements ValidSymbolPairGeneratorInterface
{
    public function __construct(
        private SymbolRepositoryInterface $symbolRepository,
        private SymbolPairValidatorInterface $symbolPairValidator,
    ) {
    }

    /** @return iterable<array{base: Symbol, quote: Symbol}> */
    public function generate(): iterable
    {
        /** @var Symbol[] $symbols */
        $symbols = $this->symbolRepository->findAll();

        foreach ($symbols as $base) {
            foreach ($symbols as $quote) {
                if ($this->isSameSymbol($base, $quote)) {
                    continue;
                }

                if ($this->symbolPairValidator->isValid($base, $quote)) {
                    yield ['base' => $base, 'quote' => $quote];
                }
            }
        }
    }

    private function isSameSymbol(Symbol $a, Symbol $b): bool
    {
        return $a === $b;
    }
}
