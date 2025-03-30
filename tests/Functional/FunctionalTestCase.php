<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class FunctionalTestCase extends WebTestCase
{
    private const USER_EMAIL = 'admin@example.com';

    private const USER_PASSWORD = 'root';

    protected static bool $fixturesLoaded = false;

    private ?string $token = null;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->ensureFixturesLoaded();
    }

    protected function getJwtToken(
        string $email = self::USER_EMAIL,
        string $password = self::USER_PASSWORD
    ): ?string {
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
        $headers = [
            'CONTENT_TYPE' => 'application/ld+json',
            'HTTP_ACCEPT' => 'application/ld+json',
        ];

        if ($token) {
            $headers['HTTP_Authorization'] = sprintf('Bearer %s', $token);
        }

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            $headers,
            json_encode($data)
        );

        return $this->client;
    }

    protected function getTestContainer(): ContainerInterface
    {
        return $this->client->getContainer();
    }

    private function ensureFixturesLoaded(): void
    {
        if (self::$fixturesLoaded) {
            return;
        }

        $application = new Application($this->client->getKernel());
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
