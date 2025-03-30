<?php

declare(strict_types=1);

namespace App\Integration\Binance;

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
        $data = $response->toArray(false);

        if (!isset($data['price'])) {
            throw new RuntimeException(
                sprintf('No exchange rate data for %s currency', $symbol)
            );
        }

        return (float) $data['price'];
    }
}
