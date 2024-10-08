<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Handler;

use Psr\Http\Message\ResponseInterface;

interface ResponseMutationHandlerInterface
{
    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function handleResponse(ResponseInterface $response): ResponseInterface;
}
