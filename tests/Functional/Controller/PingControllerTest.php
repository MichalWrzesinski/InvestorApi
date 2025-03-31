<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PingControllerTest extends FunctionalTestCase
{
    public function testPingEndpointReturnsStatusOk(): void
    {
        $client = $this->requestJson(Request::METHOD_GET, '/api/ping');

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        $this->assertJson($content);

        /** @var array<string, mixed> $actual */
        $actual = json_decode($content, true);

        $expected = ['status' => Response::HTTP_OK];
        $this->assertSame($expected, $actual);
    }
}
