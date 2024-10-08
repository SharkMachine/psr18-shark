<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Handler;

use Countable;
use InvalidArgumentException;
use Iterator;

/**
 * @implements Iterator<ResponseMutationHandlerInterface>
 */
class ResponseMutationHandlerCollection implements Iterator
{
    /**
     * @var array<int, ResponseMutationHandlerInterface>
     */
    private array $handlers;

    /**
     * @var int
     */
    private int $position = 0;

    /**
     * @param (Countable&Iterator<ResponseMutationHandlerInterface>)|array<ResponseMutationHandlerInterface> $handlers
     */
    public function __construct((Countable&Iterator)|array $handlers)
    {
        if (count($handlers) === 0) {
            throw new InvalidArgumentException('At least one handler must be provided');
        }
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }
    }

    /**
     * @param ResponseMutationHandlerInterface $handler
     *
     * @return void
     */
    private function addHandler(ResponseMutationHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * @return ResponseMutationHandlerInterface
     */
    public function current(): ResponseMutationHandlerInterface
    {
        return $this->handlers[$this->position];
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return array_key_exists($this->position, $this->handlers);
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}
