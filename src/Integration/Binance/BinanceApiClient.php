<?php

declare(strict_types=1);

namespace App\Integration\Binance;

use App\Enum\SymbolTypeEnum;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class BinanceApiClient implements BinanceApiClientInterface
{
    private const URL = 'https://api.binance.com/api/v3/ticker/price?symbol=%s';

    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    public function getPriceForPair(SymbolTypeEnum $type, string $base, string $quote): float
    {
        if ('USDC' === $base) {
            return 1.0;
        }

        if ('USD' === strtoupper($quote)) {
            $quote = 'USDC';
        }

        $symbol = strtoupper($base.$quote);
        $url = sprintf(self::URL, $symbol);

        $response = $this->httpClient->request('GET', $url);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException(sprintf('Binance API error (%d) for symbol %s', $response->getStatusCode(), $symbol));
        }

        $data = $response->toArray(false);

        if (!isset($data['price'])) {
            throw new \RuntimeException(sprintf('No exchange rate data for %s currency', $symbol));
        }

        return (float) $data['price'];
    }
}
