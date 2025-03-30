<?php

declare(strict_types=1);

namespace App\Integration\Stooq;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use RuntimeException;

final class StooqApiClient
{
    private const URL = 'https://stooq.com/q/l/?s=%s&f=sd2t2ohlcv&h&e=csv';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {}

    public function getPriceForSymbol(string $symbol): float
    {
        $url = sprintf(self::URL, strtolower($symbol));
        $response = $this->httpClient->request('GET', $url);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new RuntimeException(
                sprintf(
                    'Stooq API error (%d) for symbol %s',
                    $response->getStatusCode(),
                    $symbol
                )
            );
        }

        $csv = $response->getContent();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        if (!isset($rows[1][6]) || $rows[1][6] === 'N/D') {
            throw new RuntimeException(
                sprintf('No exchange rate data for %s currency', $symbol)
            );
        }

        return (float) $rows[1][6];
    }
}
