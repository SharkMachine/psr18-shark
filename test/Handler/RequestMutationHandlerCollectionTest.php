<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark\Testing\Handler;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestInterface;
use SharkMachine\Psr18Shark\Handler\RequestMutationHandlerCollection;
use SharkMachine\Psr18Shark\Handler\RequestMutationHandlerInterface;
use SharkMachine\Psr18Shark\Testing\TestCase;
use stdClass;
use TypeError;

final class RequestMutationHandlerCollectionTest extends TestCase
{
    public function testHandlers(): void
    {
        $handlerOne = new class implements RequestMutationHandlerInterface {
            public function handleRequest(RequestInterface $request): RequestInterface
            {
                return (new Psr17Factory())->createRequest('GET', 'https://example.com')->withHeader('X-Foo', 'bar');
            }
        };
        $handlerTwo = new class implements RequestMutationHandlerInterface {
            public function handleRequest(RequestInterface $request): RequestInterface
            {
                return (new Psr17Factory())->createRequest('GET', 'https://example.com')->withHeader('X-Foo', 'bar');
            }
        };

        $handlerCollection = new RequestMutationHandlerCollection([$handlerOne, $handlerTwo]);
        foreach ($handlerCollection as $index => $handler) {
            if ($index === 0) {
                self::assertSame($handlerOne, $handler);
            } else {
                self::assertSame($handlerTwo, $handler);
            }
            $request = $handler->handleRequest((new Psr17Factory())->createRequest('GET', 'https://example.com'));
            self::assertSame('bar', $request->getHeaderLine('X-Foo'));
        }
    }

    public function testIncorrectHandler(): void
    {
        try {
            new RequestMutationHandlerCollection([new stdClass()]);
            self::fail('Expected TypeError');
        } catch (TypeError $e) {
            self::assertStringContainsString('must be of type ' . RequestMutationHandlerInterface::class . ', stdClass given', $e->getMessage());
        }
    }
}
