<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ExchangeRate;
use App\Repository\ExchangeRateRepository;
use App\Repository\SymbolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:synchronize-exchange-rates')]
final class SynchronizeExchangeRatesCommand extends Command
{
    public function __construct(
        private readonly SymbolRepository $symbolRepository,
        private readonly ExchangeRateRepository $exchangeRateRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symbols = $this->symbolRepository->findAll();
        $createdCount = 0;

        foreach ($symbols as $base) {
            foreach ($symbols as $quote) {
                if ($base === $quote) {
                    continue;
                }

                if (!$this->exchangeRateRepository->exists($base, $quote)) {
                    $exchangeRate = (new ExchangeRate())
                        ->setBase($base)
                        ->setQuote($quote)
                        ->setPrice(0.0);

                    $this->entityManager->persist($exchangeRate);
                    $createdCount++;
                }
            }
        }

        $this->entityManager->flush();

        $output->writeln(sprintf('%d new Exchange Rate records created', $createdCount));

        return Command::SUCCESS;
    }
}
