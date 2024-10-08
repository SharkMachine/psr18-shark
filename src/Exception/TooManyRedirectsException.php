<?php

namespace SharkMachine\Psr18Shark\Exception;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

class TooManyRedirectsException extends Exception implements ClientExceptionInterface
{
}
