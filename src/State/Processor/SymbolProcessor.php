<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Symbol;
use App\Service\ExchangeRateSynchronizerInterface;

/** @implements ProcessorInterface<object, object> */
final class SymbolProcessor implements ProcessorInterface
{
    /** @param ProcessorInterface<Symbol, Symbol> $persistProcessor */
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly ExchangeRateSynchronizerInterface $synchronizer,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Symbol) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        $this->synchronizer->synchronizeFor($data);

        return $result;
    }
}
