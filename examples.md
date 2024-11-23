# Usage examples

The examples here use `nyholm/psr7`. You are free to use any PSR-7 and PSR-17 library instead.

## Basic request

```php
<?php

declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use SharkMachine\Psr18Shark\Client;

$factory = new Psr17Factory();
$client = new Client($factory, $factory);

// The returned object is a PSR-7 request. Read more about it at https://www.php-fig.org/psr/psr-7/
$request = $factory->createRequest('POST', 'https://request-uri');

// You van add headers and request body here
$request->getBody()->write('Message');
$request = $request->withHeader('X-MyHeader', 'Data');

// Send the request to get the response. The response is a PSR-7 response.
$response = $client->sendRequest($request);
```