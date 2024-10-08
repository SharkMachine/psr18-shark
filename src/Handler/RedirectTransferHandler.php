<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Handler;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use SharkMachine\Psr18Shark\Client;
use SharkMachine\Psr18Shark\Exception\TooManyRedirectsException;
use Throwable;

class RedirectTransferHandler implements TransferHandlerInterface
{
    /**
     * @var int
     */
    private int $redirects = 0;

    /**
     * @param UriFactoryInterface $uriFactory
     * @param int                 $maxRedirects
     */
    public function __construct(
        private readonly UriFactoryInterface $uriFactory,
        private readonly int $maxRedirects = 5
    ) {
    }

    /**
     * @template T of Throwable
     *
     * @param T $e
     *
     * @return void
     *
     * @throws T
     */
    public function handleException(Throwable $e): void
    {
        throw $e;
    }

    /**
     * @param Client $client
     * @param RequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     *
     * @throws ClientExceptionInterface
     * @throws TooManyRedirectsException
     */
    public function handleResponse(
        Client $client,
        RequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $responseCode = $response->getStatusCode();
        if ($responseCode >= 301 && $responseCode <= 302) {
            $this->redirects++;
            if ($this->redirects > $this->maxRedirects) {
                throw new TooManyRedirectsException('Too many redirects');
            }
            $request  = $request->withUri($this->uriFactory->createUri($response->getHeaderLine('Location')));
            $response = $client->sendRequest($request);
        }
        return $response;
    }
}
