<?php

declare(strict_types=1);

namespace App\Command;

use App\Generator\ValidSymbolPairGeneratorInterface;
use App\Message\UpdateRatesMessage;
use App\Resolver\ProcessorResolver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'app:init-exchange-rate-updates')]
final class InitExchangeRateUpdateCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly ValidSymbolPairGeneratorInterface $pairGenerator,
        private readonly ProcessorResolver $processorResolver,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string, array<array{base: string, quote: string}>> $grouped */
        $grouped = [];

        foreach ($this->pairGenerator->generate() as $pair) {
            $base = $pair['base'];
            $quote = $pair['quote'];

            $processor = $this->processorResolver->resolve($base->getType()->value);

            if (!$processor) {
                continue;
            }

            $grouped[$processor][] = [
                'base' => $base->getSymbol(),
                'quote' => $quote->getSymbol(),
            ];
        }

        foreach ($grouped as $processor => $pairs) {
            $this->bus->dispatch(new UpdateRatesMessage($processor, $pairs));
        }

        $output->writeln('Rate update tasks were initiated from data in the database.');

        return Command::SUCCESS;
    }
}
