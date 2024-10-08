<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Handler;

use Countable;
use InvalidArgumentException;
use Iterator;

/**
 * @implements Iterator<RequestMutationHandlerInterface>
 */
class RequestMutationHandlerCollection implements Iterator
{
    /**
     * @var array<int, RequestMutationHandlerInterface>
     */
    private array $handlers;

    /**
     * @var int
     */
    private int $position = 0;

    /**
     * @param Countable&Iterator<RequestMutationHandlerInterface>|array<RequestMutationHandlerInterface> $handlers
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
     * @param RequestMutationHandlerInterface $handler
     *
     * @return void
     */
    private function addHandler(RequestMutationHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * @return RequestMutationHandlerInterface
     */
    public function current(): RequestMutationHandlerInterface
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
