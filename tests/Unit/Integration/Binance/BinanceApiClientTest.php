<?php

declare(strict_types=1);

namespace App\Tests\Unit\Integration\Binance;

use App\Enum\SymbolTypeEnum;
use App\Integration\Binance\BinanceApiClient;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class BinanceApiClientTest extends TestCase
{
    public function testReturnsPriceForValidResponse(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $httpClient
            ->method('request')
            ->with('GET', 'https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDC')
            ->willReturn($response);

        $response
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->method('toArray')
            ->with(false)
            ->willReturn(['price' => '26123.99']);

        $client = new BinanceApiClient($httpClient);

        $price = $client->getPriceForPair(SymbolTypeEnum::CRYPTO, 'btc', 'usd');

        $this->assertSame(26123.99, $price);
    }

    public function testReturnsOneWhenBaseIsUsdc(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $client = new BinanceApiClient($httpClient);

        $price = $client->getPriceForPair(SymbolTypeEnum::CRYPTO, 'USDC', 'BTC');

        $this->assertSame(1.0, $price);
    }

    public function testThrowsExceptionForNon200Response(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Binance API error (500) for symbol BTCUSDC');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $httpClient
            ->method('request')
            ->willReturn($response);

        $response
            ->method('getStatusCode')
            ->willReturn(500);

        $client = new BinanceApiClient($httpClient);
        $client->getPriceForPair(SymbolTypeEnum::CRYPTO, 'BTC', 'USD');
    }

    public function testThrowsExceptionWhenPriceMissing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No exchange rate data for BTCUSDC currency');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $httpClient
            ->method('request')
            ->willReturn($response);

        $response
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->method('toArray')
            ->with(false)
            ->willReturn([]);

        $client = new BinanceApiClient($httpClient);
        $client->getPriceForPair(SymbolTypeEnum::CRYPTO, 'BTC', 'USD');
    }
}
