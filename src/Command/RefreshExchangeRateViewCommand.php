<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'app:exchange-rate:refresh-materialized-view')]
final class RefreshExchangeRateViewCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->connection->executeStatement('REFRESH MATERIALIZED VIEW CONCURRENTLY exchange_rate_latest');
            $output->writeln('Exchange_rate_latest view refreshed');

            return Command::SUCCESS;

        } catch (Throwable $throwable) {
            $output->writeln(
                sprintf(
                    'Exchange_rate_latest view  not refreshed: %s',
                    $throwable->getMessage()
                )
            );

            return Command::FAILURE;
        }
    }
}
