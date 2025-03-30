<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ExchangeRate;
use App\Repository\ExchangeRateRepository;
use App\Generator\ValidSymbolPairGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:synchronize-exchange-rates')]
final class SynchronizeExchangeRatesCommand extends Command
{
    public function __construct(
        private readonly ValidSymbolPairGenerator $pairGenerator,
        private readonly ExchangeRateRepository   $exchangeRateRepository,
        private readonly EntityManagerInterface   $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $createdCount = 0;

        foreach ($this->pairGenerator->generate() as ['base' => $base, 'quote' => $quote]) {
            if (!$this->exchangeRateRepository->exists($base, $quote)) {
                $exchangeRate = (new ExchangeRate())
                    ->setBase($base)
                    ->setQuote($quote)
                    ->setPrice(0.0);

                $this->entityManager->persist($exchangeRate);
                $createdCount++;
            }
        }

        $this->entityManager->flush();

        $output->writeln(sprintf('%d new exchange rate records created.', $createdCount));

        return Command::SUCCESS;
    }
}
