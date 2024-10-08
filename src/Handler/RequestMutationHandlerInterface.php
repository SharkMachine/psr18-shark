<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Handler;

use Psr\Http\Message\RequestInterface;

interface RequestMutationHandlerInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return RequestInterface
     */
    public function handleRequest(RequestInterface $request): RequestInterface;
}
