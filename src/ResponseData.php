<?php
declare(strict_types=1);

namespace SharkMachine\Psr18Shark;

class ResponseData
{
    /**
     * @param resource                $streamHandle
     * @param array<string, string[]> $headers
     */
    public function __construct(
        public $streamHandle,
        public array $headers = []
    ) {
    }
}
