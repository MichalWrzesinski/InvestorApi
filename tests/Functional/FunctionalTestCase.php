<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Request;

abstract class FunctionalTestCase extends WebTestCase
{
    protected static bool $fixturesLoaded = false;

    private ?string $token = null;

    private const USER_EMAIL = 'example@example.com';

    private const USER_PASSWORD = 'root';

    protected function getJwtToken(string $email = self::USER_EMAIL, string $password = self::USER_PASSWORD): ?string
    {
        $client = $this->requestJson(Request::METHOD_POST, '/api/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        return $this->token = $data['token'] ?? null;
    }

    protected function requestJson(
        string $method,
        string $uri,
        array $data = [],
        ?string $token = null,
    ): KernelBrowser {
        $client = static::createClient();
        $this->ensureFixturesLoaded();

        $headers = ['CONTENT_TYPE' => 'application/json'];

        if ($token) {
            $headers['HTTP_Authorization'] = sprintf('Bearer %s', $token);
        }

        $client->request(
            $method,
            $uri,
            [],
            [],
            $headers,
            json_encode($data)
        );

        return $client;
    }

    private function ensureFixturesLoaded(): void
    {
        if (self::$fixturesLoaded) {
            return;
        }

        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $application->run(
            new ArrayInput([
                'command' => 'hautelook:fixtures:load',
                '--env' => 'test',
                '--no-interaction' => true,
                '--quiet' => true,
            ])
        );

        self::$fixturesLoaded = true;
    }
}
