<?php

declare(strict_types=1);

namespace App\State\Processor\ExchangeRate;

use App\Enum\DataProcessorEnum;
use App\Enum\SymbolTypeEnum;
use App\Integration\Yahoo\YahooApiClientInterface;
use App\Repository\SymbolRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class YahooProcessor extends AbstractExchangeRateProcessor
{
    public function __construct(
        private YahooApiClientInterface $client,
        SymbolRepositoryInterface $symbolRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
    ) {
        parent::__construct($symbolRepository, $entityManager, $logger);
    }

    public function supports(DataProcessorEnum $processor): bool
    {
        return DataProcessorEnum::YAHOO === $processor;
    }

    protected function getSourceName(): string
    {
        return 'Yahoo';
    }

    protected function getPriceForPair(SymbolTypeEnum $type, string $base, string $quote): float
    {
        return $this->client->getPriceForPair($type, $base, $quote);
    }
}
