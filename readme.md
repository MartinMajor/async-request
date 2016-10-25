AsyncRequest
===========================

> Asynchronous cURL library for PHP with reasonable API. 

[![Build Status](https://travis-ci.org/MartinMajor/async-request.svg?branch=master)](https://travis-ci.org/MartinMajor/async-request)

PHP is by default single-thread language but when it comes to HTTP requests it is not very convenient to do them in serial. cURL implementation in PHP offers functions for multi requests but with terrible C-style API. This library wraps those functions into modern object-oriented event-driven API. 

Simple example
--------------

```php
$urls = [
	'http://www.example.com',
	'http://www.example.org',
];

$asyncRequest = new AsyncRequest\AsyncRequest();

foreach ($urls as $url) {
	$request = new AsyncRequest\Request($url);
	$asyncRequest->enqueue($request, function(AsyncRequest\Response $response) {
		echo $response->getBody() . "\n";
	});
}

$asyncRequest->run();
```

Advanced features
-----------------

You can specify number of requests that can run in parallel:

```php
$asyncRequest->setParallelLimit(5);
```

You can add other requests in callback function:

```php
$callback = function(AsyncRequest\Response $response, AsyncRequest\IRequest $request,
		AsyncRequest\AsyncRequest $asyncRequest) {
	$asyncRequest->enqueue(new AsyncRequest\Request('http://www.example.com'));
};
```

You can specify priority of each request and requests with higher priority will be called first:

```php
$asyncRequest->enqueueWithPriority(10, $request, 'callback');
```

If you want to use some cURL options, it is as easy as this:

```php
$request = new AsyncRequest\Request($url);
$request->setOption(CURLOPT_POST, true);
```

And if you want some special behavior or some additional data in `Response`, you can always create your own `Request` object by implementing `IRequest` interface.
