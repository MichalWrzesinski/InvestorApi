<?php

declare(strict_types=1);

namespace App\Integration\Binance;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use RuntimeException;

final class BinanceApiClient
{
    private const URL = 'https://api.binance.com/api/v3/ticker/price?symbol=%s';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {}

    public function getPriceForPair(string $base, string $quote): float
    {
        $symbol = strtoupper($base . $quote);
        $url = sprintf(self::URL, $symbol);

        $response = $this->httpClient->request('GET', $url);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new RuntimeException(
                sprintf(
                    'Binance API error (%d) for symbol %s',
                    $response->getStatusCode(),
                    $symbol
                )
            );
        }

        $data = $response->toArray(false);

        if (!isset($data['price'])) {
            throw new RuntimeException(
                sprintf('No exchange rate data for %s currency', $symbol)
            );
        }

        return (float) $data['price'];
    }
}
