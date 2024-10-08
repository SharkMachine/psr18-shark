<?php

/**
 * Unhandled exceptions inspections are not relevant in a unit test class
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Testing\Handler;

use Mockery;
use Mockery\MockInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use SharkMachine\Psr18Shark\Client;
use SharkMachine\Psr18Shark\Exception\TooManyRedirectsException;
use SharkMachine\Psr18Shark\Handler\RedirectTransferHandler;
use SharkMachine\Psr18Shark\Handler\TransferHandlerCollection;
use SharkMachine\Psr18Shark\Testing\TestCase;

#[CoversClass(RedirectTransferHandler::class)]
class RedirectTransferHandlerTest extends TestCase
{
    /**
     * @param int $statusCode
     *
     * @return void
     */
    #[TestWith([301])]
    #[TestWith([302])]
    public function testRedirect(int $statusCode): void
    {
        $firstResponse = Mockery::mock(ResponseInterface::class);
        $firstResponse->expects('getStatusCode')->andReturn($statusCode);
        $firstResponse->expects('getHeaderLine')->with('Location')->andReturn('https://new-loc.com');
        $firstResponse->expects('getBody')->never();

        $secondResponse = Mockery::mock(ResponseInterface::class);
        $secondResponse->expects('getStatusCode')->andReturn(200);
        $secondResponseStream = Mockery::mock(StreamInterface::class);
        $secondResponseStream->expects('__toString')->andReturn('Should be in the response');
        $secondResponse->expects('getBody')->andReturn($secondResponseStream);

        $request = Mockery::mock(RequestInterface::class);
        $request->expects('withUri')->with('https://new-loc.com')->andReturn($request);

        $client = Mockery::mock(Client::class);
        $client->expects('sendRequest')->andReturn($secondResponse);
        $handler = new RedirectTransferHandler(new Psr17Factory());

        $response = $handler->handleResponse($client, $request, $firstResponse);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Should be in the response', (string)$response->getBody());
    }

    public function testRedirectLimit(): void
    {
        $handler = new RedirectTransferHandler(new Psr17Factory(), 1);

        $firstResponse = Mockery::mock(ResponseInterface::class);
        $firstResponse->expects('getStatusCode')->andReturn(301);
        $firstResponse->expects('getHeaderLine')->with('Location')->andReturn('https://new-loc.com');
        $firstResponse->expects('getBody')->never();

        $secondResponse = Mockery::mock(ResponseInterface::class);
        $secondResponse->expects('getStatusCode')->andReturn(301);
        $secondResponse->expects('getHeaderLine')->with('Location')->never();
        $secondResponse->expects('getBody')->never();

        $request = new Request('GET', 'https://new-loc.com');

        $factory = new Psr17Factory();
        /** @var MockInterface&ClientInterface $client */
        $client = Mockery::mock(
                Client::class . '[initCurl,curlRequest,getResponse]',
                [
                    $factory,
                    $factory,
                    null,
                    null,
                    new TransferHandlerCollection([$handler])
                ]
            )
            ->shouldAllowMockingProtectedMethods();
        $client->expects('getResponse')->andReturn($secondResponse);
        $client->expects('initCurl')->once();
        $client->expects('curlRequest')->once();

        try {
            $handler->handleResponse($client, $request, $firstResponse);
            self::fail(TooManyRedirectsException::class . ' should be thrown');
        } catch (TooManyRedirectsException $ex) {
            self::assertSame('Too many redirects', $ex->getMessage());
        }
    }

    /**
     * @param int $statusCode
     *
     * @return void
     */
    #[TestWith([200])]
    #[TestWith([400])]
    #[TestWith([500])]
    public function testNoRedirect(int $statusCode): void
    {
        $expectedResponse = Mockery::mock(ResponseInterface::class);
        $expectedResponse->expects('getStatusCode')->andReturn($statusCode)->times(2);
        $expectedResponse->expects('getHeaderLine')->never();
        $responseStream = Mockery::mock(StreamInterface::class);
        $responseStream->expects('__toString')->andReturn('Should be in the response');
        $expectedResponse->expects('getBody')->andReturn($responseStream);

        $request = Mockery::mock(RequestInterface::class);
        $request->expects('withUri')->never();

        $client = Mockery::mock(Client::class);
        $client->expects('sendRequest')->never();
        $handler = new RedirectTransferHandler(new Psr17Factory());

        $response = $handler->handleResponse($client, $request, $expectedResponse);
        self::assertSame($statusCode, $response->getStatusCode());
        self::assertSame('Should be in the response', (string)$response->getBody());
        self::assertSame($expectedResponse, $response);
    }
}
