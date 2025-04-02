<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Symbol;
use App\Service\ExchangeRateSynchronizerInterface;

/** @implements ProcessorInterface<object, object> */
final readonly class SymbolProcessor implements ProcessorInterface
{
    /** @param ProcessorInterface<Symbol, Symbol> $persistProcessor */
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private ExchangeRateSynchronizerInterface $synchronizer,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Symbol) {
            $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);
            $this->synchronizer->synchronizeFor($data);

            return $result;
        }

        /** @var ProcessorInterface<object, object> $fallback */
        $fallback = $this->persistProcessor;

        return $fallback->process($data, $operation, $uriVariables, $context);
    }
}
