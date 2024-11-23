<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Exception;

use Exception;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class BadRequestException extends Exception implements HttpStatusErrorInterface, RequestExceptionInterface
{
    /**
     * @param int               $statusCode
     * @param ResponseInterface $response
     * @param RequestInterface  $request
     * @param string            $message
     * @param int               $code
     * @param Throwable|null    $previous
     */
    public function __construct(
        private readonly int $statusCode,
        private readonly ResponseInterface $response,
        private readonly RequestInterface $request,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
