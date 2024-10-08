<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpStatusErrorInterface extends ClientExceptionInterface
{
    /**
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface;
}
