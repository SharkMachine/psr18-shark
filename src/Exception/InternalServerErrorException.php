<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Exception;

use Exception;
use Psr\Http\Message\ResponseInterface;

final class InternalServerErrorException extends Exception implements HttpStatusErrorInterface
{
    /**
     * @param int               $statusCode
     * @param ResponseInterface $response
     * @param string            $message
     * @param int               $code
     * @param Exception|null    $previous
     */
    public function __construct(
        private readonly int $statusCode,
        private readonly ResponseInterface $response,
        string $message = "",
        int $code = 0,
        ?Exception $previous = null
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
}
