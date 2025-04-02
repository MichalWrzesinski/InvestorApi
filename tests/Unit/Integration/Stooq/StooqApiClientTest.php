<?php

declare(strict_types=1);

namespace App\Tests\Unit\Integration\Stooq;

use App\Enum\SymbolTypeEnum;
use App\Integration\Stooq\StooqApiClient;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class StooqApiClientTest extends TestCase
{
    public function testReturnsPriceFromCsv(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $csv = <<<CSV
            Symbol,Date,Time,Open,High,Low,Close,Volume
            eurusd,2024-01-01,12:00:00,1.08,1.09,1.07,1.0850,1000000
        CSV;

        $httpClient
            ->method('request')
            ->with('GET', 'https://stooq.com/q/l/?s=eurusd&f=sd2t2ohlcv&h&e=csv')
            ->willReturn($response);

        $response
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->method('getContent')
            ->willReturn($csv);

        $client = new StooqApiClient($httpClient);

        $price = $client->getPriceForPair(SymbolTypeEnum::FIAT, 'eur', 'usd');

        $this->assertSame(1.0850, $price);
    }

    public function testThrowsExceptionForNon200Response(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stooq API error (500) for symbol eurusd');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $httpClient
            ->method('request')
            ->willReturn($response);

        $response
            ->method('getStatusCode')
            ->willReturn(500);

        $client = new StooqApiClient($httpClient);
        $client->getPriceForPair(SymbolTypeEnum::FIAT, 'eur', 'usd');
    }

    public function testThrowsExceptionWhenPriceIsMissing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No exchange rate data for eurusd currency');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $csv = <<<CSV
            Symbol,Date,Time,Open,High,Low,Close,Volume
            eurusd,2024-01-01,12:00:00,1.08,1.09,1.07,N/D,1000000
        CSV;

        $httpClient
            ->method('request')
            ->willReturn($response);

        $response
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->method('getContent')
            ->willReturn($csv);

        $client = new StooqApiClient($httpClient);
        $client->getPriceForPair(SymbolTypeEnum::FIAT, 'eur', 'usd');
    }
}
