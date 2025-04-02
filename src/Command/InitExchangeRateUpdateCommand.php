<?php

declare(strict_types=1);

namespace App\Command;

use App\Generator\ValidSymbolPairGeneratorInterface;
use App\Message\UpdateRatesMessage;
use App\Resolver\ProcessorResolverInterface;
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
        private readonly ProcessorResolverInterface $processorResolver,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dispatchCount = 0;

        foreach ($this->pairGenerator->generate() as $pair) {
            $type = $pair['base']->getType()->value;
            $processor = $this->processorResolver->resolve($type);

            if (!$processor) {
                continue;
            }

            $this->bus->dispatch(
                new UpdateRatesMessage(
                    $type,
                    $processor,
                    $pair['base']->getSymbol(),
                    $pair['quote']->getSymbol()
                )
            );

            ++$dispatchCount;
        }

        $output->writeln(sprintf('%d rate update tasks initiated', $dispatchCount));

        return Command::SUCCESS;
    }
}
