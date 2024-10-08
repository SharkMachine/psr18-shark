<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Handler;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SharkMachine\Psr18Shark\Client;
use Throwable;

interface TransferHandlerInterface
{
    /**
     * @param Throwable $e
     *
     * @return void
     *
     * @throws ClientExceptionInterface
     */
    public function handleException(Throwable $e): void;

    /**
     * @param Client            $client
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     *
     * @throws ClientExceptionInterface
     */
    public function handleResponse(
        Client $client,
        RequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface;
}
