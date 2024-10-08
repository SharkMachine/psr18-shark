<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Exception;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

final class CurlException extends Exception implements ClientExceptionInterface
{
}
