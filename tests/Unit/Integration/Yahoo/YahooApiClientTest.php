<?php

declare(strict_types=1);

namespace App\Tests\Unit\Integration\Yahoo;

use App\Enum\SymbolTypeEnum;
use App\Integration\Yahoo\YahooApiClient;
use App\Integration\Yahoo\YahooSymbolBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class YahooApiClientTest extends TestCase
{
    public function testReturnsPriceForValidResponse(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $symbolBuilder = new YahooSymbolBuilder();

        $httpClient
            ->method('request')
            ->with(
                'GET',
                'https://query1.finance.yahoo.com/v8/finance/chart/USDPLN=X?range=1d&interval=1d',
                [
                    'headers' => ['User-Agent' => 'Mozilla/5.0'],
                ]
            )
            ->willReturn($response);

        $response
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->method('toArray')
            ->with(false)
            ->willReturn([
                'chart' => [
                    'result' => [
                        [
                            'meta' => [
                                'regularMarketPrice' => 4.35,
                            ],
                        ],
                    ],
                ],
            ]);

        $client = new YahooApiClient($httpClient, $symbolBuilder);

        $price = $client->getPriceForPair(SymbolTypeEnum::FIAT, 'usd', 'pln');

        $this->assertSame(4.35, $price);
    }

    public function testThrowsExceptionForNon200Response(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Yahoo API error (500) for symbol USDPLN=X');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $symbolBuilder = new YahooSymbolBuilder();

        $httpClient
            ->method('request')
            ->willReturn($response);

        $response
            ->method('getStatusCode')
            ->willReturn(500);

        $client = new YahooApiClient($httpClient, $symbolBuilder);
        $client->getPriceForPair(SymbolTypeEnum::FIAT, 'usd', 'pln');
    }

    public function testThrowsExceptionWhenPriceMissing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No exchange rate data for USDPLN=X currency');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $symbolBuilder = new YahooSymbolBuilder();

        $httpClient
            ->method('request')
            ->willReturn($response);

        $response
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->method('toArray')
            ->willReturn([
                'chart' => [
                    'result' => [
                        [
                            'meta' => [],
                        ],
                    ],
                ],
            ]);

        $client = new YahooApiClient($httpClient, $symbolBuilder);
        $client->getPriceForPair(SymbolTypeEnum::FIAT, 'usd', 'pln');
    }
}
