<?php

namespace App\Command;

use App\Enum\DataProcessor;
use App\Enum\SymbolType;
use App\Generator\ValidPairGeneratorInterface;
use App\Message\UpdateRatesMessage;
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
        private readonly ValidPairGeneratorInterface $pairGenerator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string, array<array{0: string, 1: string}>> $grouped */
        $grouped = [];

        foreach ($this->pairGenerator->generate() as $pair) {
            $base = $pair['base'];
            $quote = $pair['quote'];

            $processor = $this->resolveProcessor($base->getType()->value, $quote->getType()->value);

            if (!$processor) {
                continue;
            }

            $grouped[$processor][] = [$base->getCode(), $quote->getCode()];
        }

        foreach ($grouped as $processor => $pairs) {
            $this->bus->dispatch(new UpdateRatesMessage($processor, $pairs));
        }

        $output->writeln('Rate update tasks were initiated from data in the database.');

        return Command::SUCCESS;
    }

    private function resolveProcessor(string $baseType, string $quoteType): ?string
    {
        return match (true) {
            $quoteType === 'PLN' && $baseType === SymbolType::FIAT => DataProcessor::NBP->value,
            $baseType === SymbolType::CRYPTO || $quoteType === SymbolType::CRYPTO => DataProcessor::BINANCE->value,
            $baseType === SymbolType::STOCK || $baseType === SymbolType::ETF => DataProcessor::YAHOO->value,
            default => null,
        };
    }
}
