<?php

declare(strict_types=1);

namespace App\Integration\Nbp;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use RuntimeException;

final class NbpApiClient
{
    private const URL = 'https://api.nbp.pl/api/exchangerates/rates/a/%s/?format=json';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {}

    public function getMidRate(string $symbol): float
    {
        $url = sprintf(self::URL, strtolower($symbol));
        $response = $this->httpClient->request('GET', $url);
        $data = $response->toArray(false);

        if (!isset($data['rates'][0]['mid'])) {
            throw new RuntimeException(
                sprintf('No exchange rate data for %s currency', $symbol)
            );
        }

        return (float) $data['rates'][0]['mid'];
    }
}
