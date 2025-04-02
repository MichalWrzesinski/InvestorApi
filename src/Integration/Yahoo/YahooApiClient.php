<?php

declare(strict_types=1);

namespace App\Integration\Yahoo;

use App\Enum\SymbolTypeEnum;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class YahooApiClient implements YahooApiClientInterface
{
    private const URL = 'https://query1.finance.yahoo.com/v8/finance/chart/%s?range=1d&interval=1d';
    private const HEADERS = ['User-Agent' => 'Mozilla/5.0'];

    public function __construct(
        private HttpClientInterface $httpClient,
        private YahooSymbolBuilder $symbolBuilder,
    ) {
    }

    public function getPriceForPair(SymbolTypeEnum $type, string $base, string $quote): float
    {
        $symbol = $this->symbolBuilder->build($type, $base, $quote);
        $url = sprintf(self::URL, $symbol);

        $response = $this->httpClient->request('GET', $url, ['headers' => self::HEADERS]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException(sprintf('Yahoo API error (%d) for symbol %s', $response->getStatusCode(), $symbol));
        }

        $data = $response->toArray(false);
        $price = $data['chart']['result'][0]['meta']['regularMarketPrice'] ?? null;

        if (!is_numeric($price)) {
            throw new \RuntimeException(sprintf('No exchange rate data for %s currency', $symbol));
        }

        return (float) $price;
    }
}
