<?php

declare(strict_types=1);

namespace App\Generator;

use App\Entity\Symbol;
use App\Repository\SymbolRepository;
use App\Validator\SymbolPairValidatorInterface;

final class ValidPairGenerator implements ValidPairGeneratorInterface
{
    public function __construct(
        private readonly SymbolRepository $symbolRepository,
        private readonly SymbolPairValidatorInterface $symbolPairValidator,
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

                if ($this->symbolPairValidator->isValid($base->getType(), $quote->getType())) {
                    yield ['base' => $base, 'quote' => $quote];
                }
            }
        }
    }
}
