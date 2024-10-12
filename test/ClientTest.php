<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Testing;

use Nyholm\Psr7\Factory\Psr17Factory;
use SharkMachine\Psr18Shark\Client;

final class ClientTest extends TestCase
{
    public function testSendRequest(): void
    {
        $factory = new Psr17Factory();
        $client = new Client($factory, $factory);
        $response = $client->sendRequest($factory->createRequest('GET', 'https://sharkma.ecxol.net'));
        $this->assertSame(200, $response->getStatusCode());
    }
}
