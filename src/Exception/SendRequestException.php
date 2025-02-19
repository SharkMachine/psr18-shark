<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Exception;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Throwable;

final class SendRequestException extends Exception implements ClientExceptionInterface
{
    /**
     * @param Throwable $previous
     */
    public function __construct(Throwable $previous)
    {
        parent::__construct('Problem with sending request: ' . $previous->getPrevious(), 0, $previous);
    }
}
