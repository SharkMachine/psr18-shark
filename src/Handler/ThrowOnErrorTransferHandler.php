<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Handler;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SharkMachine\Psr18Shark\Client;
use SharkMachine\Psr18Shark\Exception\BadRequestException;
use SharkMachine\Psr18Shark\Exception\InternalServerErrorException;
use Throwable;

class ThrowOnErrorTransferHandler implements TransferHandlerInterface
{
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
     * @param Client            $client
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     *
     * @throws BadRequestException
     * @throws InternalServerErrorException
     */
    public function handleResponse(Client $client, RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400 && $statusCode < 500) {
            throw new BadRequestException($statusCode, $response, $request);
        }
        if ($statusCode >= 500) {
            throw new InternalServerErrorException($statusCode, $response);
        }
        return $response;
    }
}
