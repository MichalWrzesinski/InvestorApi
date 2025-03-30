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

        $expected = ['status' => Response::HTTP_OK];
        $actual = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame($expected, $actual);
    }
}
