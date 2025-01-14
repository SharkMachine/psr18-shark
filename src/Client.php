<?php

declare(strict_types=1);

namespace SharkMachine\Psr18Shark;

use CurlHandle;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriInterface;
use SharkMachine\Psr18Shark\Exception\ClientException;
use SharkMachine\Psr18Shark\Exception\CurlException;
use SharkMachine\Psr18Shark\Exception\NoResponseException;
use SharkMachine\Psr18Shark\Handler\RequestMutationHandlerCollection;
use SharkMachine\Psr18Shark\Handler\ResponseMutationHandlerCollection;
use SharkMachine\Psr18Shark\Handler\TransferHandlerCollection;
use Throwable;

class Client implements ClientInterface
{
    private const string DEFAULT_USER_AGENT = 'PSR-18 Shark Client';

    /**
     * @var CurlHandle
     */
    private CurlHandle $curl;

    /**
     * @param ResponseFactoryInterface               $responseFactory
     * @param StreamFactoryInterface                 $streamFactory
     * @param RequestMutationHandlerCollection|null  $requestMutationHandlerCollection Handler collection to modify the
     *                                                                                 request before it is sent.
     * @param ResponseMutationHandlerCollection|null $responseMutationHandlerCollection Handler collection to modify the
     *                                                                                  response before it is returned.
     * @param TransferHandlerCollection|null         $transferHandlerCollection Handler collection to handle the
     *                                                                          transfer. You can inject handlers such
     *                                                                          as one to follow redirects or one to
     *                                                                          retry the request.
     * @param array<int, mixed>                      $curlOptions cURL options to set, same array structure as with
     *                                                            curl_setopt_array. Please note that some options are
     *                                                            not allowed.
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ?RequestMutationHandlerCollection $requestMutationHandlerCollection = null,
        private readonly ?ResponseMutationHandlerCollection $responseMutationHandlerCollection = null,
        private readonly ?TransferHandlerCollection $transferHandlerCollection = null,
        private readonly array $curlOptions = []
    ) {
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws ClientExceptionInterface
     * @throws Throwable
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if (null !== $this->requestMutationHandlerCollection) {
            foreach ($this->requestMutationHandlerCollection as $handler) {
                $request = $handler->handleRequest($request);
            }
        }

        try {
            $responseData = $this->initCurl($request->getUri());
            $this->curlRequest($request, $responseData);
            $response = $this->getResponse($responseData);
        } catch (Throwable $ex) {
            curl_close($this->curl);
            if (null !== $this->transferHandlerCollection) {
                foreach ($this->transferHandlerCollection as $handler) {
                    $handler->handleException($ex);
                }
            } else {
                throw $ex;
            }
        }
        if (!isset($response)) {
            throw new NoResponseException(); // The exception handlers didn't throw when there was no response.
        }
        if (null !== $this->transferHandlerCollection) {
            foreach ($this->transferHandlerCollection as $handler) {
                $response = $handler->handleResponse($this, $request, $response);
            }
        }

        if (isset($this->curl)) {
            curl_close($this->curl);
        }
        return $response;
    }

    /**
     * @param UriInterface $uri
     *
     * @return ResponseData
     *
     * @throws ClientException
     */
    protected function initCurl(UriInterface $uri): ResponseData
    {
        $this->curl = curl_init((string)$uri);
        $dataStream = fopen('php://temp', 'wb+');
        if (false === $dataStream) {
            throw new ClientException('Unable to open handle for response');
        }
        $responseData = new ResponseData($dataStream);

        // Do not follow redirects.
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, false);

        // Get response
        curl_setopt(
            $this->curl,
            CURLOPT_WRITEFUNCTION,
            static function (CurlHandle $curl, string $data) use ($responseData): int {
                $bytes = fwrite($responseData->streamHandle, $data);
                if (false === $bytes) {
                    return -1; // This will cause a cURL error
                }
                return $bytes;
            }
        );

        // Get headers
        curl_setopt(
            $this->curl,
            CURLOPT_HEADERFUNCTION,
            static function (CurlHandle $curl, string $header) use ($responseData): int {
                $len = strlen($header);
                $headerArray = explode(':', $header, 2);
                if (count($headerArray) < 2) {
                    return $len;
                }
                $headerName = strtolower(trim($headerArray[0]));
                $responseData->headers[$headerName] = [trim($headerArray[1])];

                return $len;
            }
        );

        // Set custom cURL options
        foreach ($this->curlOptions as $option => $value) {
            if (in_array(
                $option,
                [
                    CURLOPT_FOLLOWLOCATION,
                    CURLOPT_HEADER,
                    CURLOPT_WRITEFUNCTION,
                    CURLOPT_HEADERFUNCTION,
                ],
                true)
            ) {
                continue;
            }
            curl_setopt($this->curl, $option, $value);
        }

        // Set the user agent if none has been defined
        if (!in_array(CURLOPT_USERAGENT, $this->curlOptions, true)) {
            curl_setopt($this->curl, CURLOPT_USERAGENT, self::DEFAULT_USER_AGENT);
        }

        return $responseData;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseData     $responseData
     *
     * @return ResponseData
     *
     * @throws ClientExceptionInterface
     */
    protected function curlRequest(RequestInterface $request, ResponseData $responseData): ResponseData
    {
        if (count($request->getHeaders()) > 0) {
            $headers = [];
            foreach ($request->getHeaders() as $headerName => $headerValues) {
                $headers[] = sprintf(
                    "%s: %s",
                    preg_replace("/[^A-Za-z0-9-_.~]/", '', $headerName),
                    preg_replace('/[^\x20-\x7E]/', '', implode(', ', $headerValues))
                );
            }
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }
        /** @phpstan-ignore argument.type */ // Method is a non-empty string
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $request->getMethod());
        $requestBody = (string)$request->getBody();
        if ('' !== $requestBody) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $requestBody);
        }
        if (false === curl_exec($this->curl)) {
            throw new CurlException(curl_error($this->curl));
        }
        return $responseData;
    }

    /**
     * @param ResponseData $responseData
     *
     * @return ResponseInterface
     */
    protected function getResponse(ResponseData $responseData): ResponseInterface
    {
        $statusCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $response   = $this->responseFactory->createResponse($statusCode);
        $response   = $response->withBody($this->streamFactory->createStreamFromResource($responseData->streamHandle));
        foreach ($responseData->headers as $headerName => $headerValue) {
            $response = $response->withHeader($headerName, $headerValue);
        }
        $response->getBody()->rewind();
        if (null !== $this->responseMutationHandlerCollection) {
            foreach ($this->responseMutationHandlerCollection as $handler) {
                $response = $handler->handleResponse($response);
            }
        }
        return $response;
    }
}
