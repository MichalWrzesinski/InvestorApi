<?php

namespace App\Command;

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
        private readonly MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Przykładowe dane do uruchomienia
        $this->bus->dispatch(new UpdateRatesMessage('BINANCE', [['BTC', 'USDT'], ['ETH', 'USDT']]));
        $this->bus->dispatch(new UpdateRatesMessage('NBP', [['USD', 'PLN'], ['EUR', 'PLN']]));

        $output->writeln('✅ Zadania aktualizacji kursów zostały zainicjowane.');
        return Command::SUCCESS;
    }
}
