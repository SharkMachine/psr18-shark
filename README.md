# PSR-18 Shark

A simple PSR-18 curl client for PHP. You need PSR-7 and PSR-17 libraries to use this client, for example `nyholm/psr7`.

The following design choices have been made:

- The client doesn't follow redirects. You need to either handle it in the code yourself or use `RedirectTransferHandler`.
- The client doesn't throw any exceptions unless there is a curl error. If you need handling for HTTP 4xx or 5xx status codes, you either need to implement it yourself or use `ThrowOnErrorTransferHandler`.

## Installation

```bash
composer require sharkmachine/psr18-shark
```

## Supported PHP versions

| Library version  | PHP 8.2 | PHP 8.3 | PHP 8.4  |
|------------------|---------|---------|----------|
| Next major (2.0) | x       | ✓       | ✓        |
| 1.x              | ✓       | ✓       | ✓        |

## Request mutation

If you need to change the request before it is sent without relying on PSR-18 functionality, you can create mutation handlers for it. `$requestMutationHandlerCollection` constructor parameter is used to pass a collection of handlers to the client.

## Response mutation

If you need to change the response before it is returned without relying on PSR-18 functionality, you can create mutation handlers for it. `$responseMutationHandlerCollection` constructor parameter is used to pass a collection of handlers to the client.

## Transfer handlers

Transfer handlers can be used to handle exceptions thrown in the case of curl errors or to handle re-sending the request in certain situations. `$transferHandlerCollection` constructor parameter is used to pass a collection of handlers to the client.

Please note that if a response cannot be received and the handler doesn't throw an exception in this case, `NoResponseException` is thrown.

The following transfer handlers are included in the library:

- `RedirectTransferHandler` - Handler that will follow redirects.
- `ThrowOnErrorTransferHandler` - Handler that will throw an exception if the response status code is 4xx or 5xx.

## Custom curl options

You can pass custom curl options to the client with `$curlOptions` constructor parameter. Please note that the following options are ignored:

- `CURLOPT_FOLLOWLOCATION` - Redirects are not followed, please use `RedirectTransferHandler` or handle the redirects yourself.
- `CURLOPT_HEADER` - Headers are set in the request.
- `CURLOPT_WRITEFUNCTION` - The client uses this to get the response body.
- `CURLOPT_HEADERFUNCTION` - The client uses this to get the response headers.
