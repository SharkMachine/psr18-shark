<?php

/**
 * Exception inspections are not relevant in unit test classes
 *
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PhpDocMissingThrowsInspection
 */

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Testing;

use Nyholm\Psr7\Factory\Psr17Factory;
use SharkMachine\Psr18Shark\Client;

final class ClientTest extends TestCase
{
    /**
     * @return void
     */
    public function testSendRequest(): void
    {
        $factory = new Psr17Factory();
        $client = new Client($factory, $factory);
        $response = $client->sendRequest($factory->createRequest('GET', 'https://sharkma.ecxol.net'));
        self::assertSame(200, $response->getStatusCode());
    }
}
