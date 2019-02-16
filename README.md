# Guzzler

> Use PHPUnit Mock Syntax without Mocking Guzzle

Guzzle is a fantastic library for making HTTP requests, but it’s not necessarily the easiest package to use in tests. You can mock the entire thing, but then you might also have to mock dozens of methods and several return types. It’s much easier to just use Guzzle. That’s where Guzzler comes in. This package covers the process of setting up a mock handler, recording history of requests, and provides several convenience methods for creating expectations and assertions on that history.

## Example Usage

```php
use Guzzler/UsesGuzzler;
use GuzzleHttp/Client;

class SomeTest extends TestCase
{
    use UsesGuzzler;

    public $classToTest;

    public function setUp(): void
    {
        parent::setUp();
    
        $client = $this->guzzler->getClient(/** Any configs for a client */ [
            "base_uri" => "https://example.com/api"
        ]);
        
        // You can then inject this client object into your code or IOC container.
        $this->classToTest = new ClassToTest($client);
    }

    public function testSomethingWithExpectations()
    {
        $this->guzzler->expects($this->once())
            ->post("/some-url")
            ->withHeader("X-Authorization", "some-key")
            ->willRespond(new Response(201));
    
        $this->classToTest->someMethod();
    }

    public function testSomethingWithAssertions()
    {
        $this->guzzler->queueResponse(
            new Response(204),
            new \Exception("Some message"),
            // any needed responses to return from the client.
        );
    
        $this->classToTest->someMethod();
        // ... Some other number of calls
    
        $this->guzzler->assertAll(function (Expectation $ex) {
            return $ex->withHeader("Authorization", "some-key");
        });
    }
}
```

## Getting Started

Add the dependency to your *composer.json* file.

```bash
composer require —dev blastcloud/guzzler
```

Add the `Guzzler\UsesGuzzler` trait to your test class.

```php
use Guzzler\UsesGuzzler;

class SomeTest extends TestCase
{
    use UsesGuzzler;
```

This trait wires up a class variable named `guzzler`. Inside that object the necessary history and mock handlers for Guzzle are instantiated and saved. You can completely customize the `Client` object however you like in two different ways.

### getClient(array $options)

The `getClient` method returns a new instance of the Guzzle `Client` class and adds any options you like to it’s constructor.

```php
$client = $this->guzzler->getClient([
    "stream" => true,
    "base_uri" => "http://some-url.com/api/v2"
]);
```

### getHandlerStack()

If you’d rather create your own `Client` instance, you can instead return the handler stack required to mock responses and add it directly to your client.

```php
$client = new Client([
    //... Some configs
    "handler" => $this->guzzler->getHandlerStack()
]);
```

You can also add your own handlers to the stack, if you’d like.

```php
$stack = $this->guzzler->getHandlerStack();
$stack->push(new SomeOtherHandler());

$client = new Client([
    "handler" => $stack
]);
```

## Mocking Responses

There are two main ways to provide responses to return from your client; `queueResponse()` directly on the `guzzler` instance, and `will()` or `willRespond()` on an expectation.

### queueResponse(...$responses)

The `queueResponse` method is the main way to add responses to your mock handler. Guzzle supports several types of responses besides the standard `ResponseInterface` object; `PromiseInterface` objects, `Exception`s, and `Callable`s. Please see the [official documentation]( http://docs.guzzlephp.org/en/stable/testing.html ) for full details on what can be passed.

```php
public function testSomething()
{
    $this->guzzler->queueResponse(new Response(201, ["some-header" => "value"], "some body"));
    
    //... whatever the first request sent to your client is, the response above will be returned.
}
```

The method accepts variadic arguments, so you can add as many responses as you like..

```php
// One call with multiple arguments
$promise = new Promise();
$this->guzzler->queueResponse(
    new Response(400),
    $promise
);

// Multiple calls with one response each.
$this->guzzler->queueResponse(new Response(200));
$this->guzzler->queueResponse(new \InvalidArgumentException(“message”));
```

> Be aware that whatever order you queue your responses is the order they will be returned from your client, no matter the URI or method of the request. This is a constraint of Guzzle’s mock handler.

### will($response, int $times = 1), willRespond($response, int $times = 1)

If you are using expectations in your test, you can add responses to the expectation chain with either `will()` or its alias, `willRespond()`. In both cases, you can provide a single response, promise, or otherwise and the number of times it should be added to the queue. This is so that you can make sure to add a response for each expected invocation.

```php
$this->guzzler->expects($this->atLeast(9))
    ->get("/some-uri")
    ->willRespond(new Response(200), 12);

$this->guzzler->expects($this->once())
    ->post("/another-uri")
    ->will(new \Exception("some message"));
```

If you’d like to return different responses from the same expectation, you can still chain your `will()` or `willRespond()` statements.

```php
$this->guzzler->expects($this->exactly(2))
    ->endpoint("/a-url-for-deleting", "DELETE")
    ->will(new Response(204))
    ->will(new Response(210));
```

> Be aware that whatever order you queue your responses is the order they will be returned from your client, no matter the URI or method of the request. This is a constraint of Guzzle’s mock handler.

## Expectations

### expects(InvokedRecorder $times, ?string $message)

To mimic the chainable syntax of PHPUnit mock objects, you can create expectations with PHPUnit’s own InvokedRecorders and the `expects()` method. InvokedRecorders are methods like `$this->once()`, `$this->lessThan($int)`, `$this->never()`, and so on. You may also pass an optional message to be used on errors as the second argument.

```php
public function testExample()
{
    $expectation = $this->guzzler->expects($this->once());
}
```

> All methods on expectations are chainable and can lead directly into another method. `$expectation->oneMethod()->anotherMethod()->stillAnother();`

### endpoint($uri, $verb), {verb}($uri)

To specify the endpoint and method used for an expectation, use the `endpoint()` method or any of the convenience endpoint verb methods.

```php
$this->guzzler->expects($this->once())
    ->endpoint("/some-url", "GET");
```

The following convenience verb methods are also available to shorten your endpoint expectations. `get`, `post`, `put`, `delete`,  `patch`, `options`.

```php
$this->guzzler->expects($this->any())
    ->get("/a-url-for-getting");
```

### withHeader(string $key, string|array $value)

If you would like to expect a certain header to be on a request, you can provide the header and it’s value.

```php
$this->guzzler->expects($this->once())
    ->withHeader("Authorization", "some-access-token");
```

You can also chain together multiple calls to `withHeader()` to individually add different headers. Headers can also be an array of values.

```php
$this->guzzler->expects($this->once())
    ->withHeader("Accept-Encoding", ["gzip", "deflate"])
    ->withHeader("Accept", "application/json");
```

### withHeaders(array $headers)

As a shorthand for multiple `withHeader()` calls, you can pass an array of header keys and values to `withHeaders()`.

```php
$this->guzzler->expects($this->once())
    ->withHeaders([
        "Accept-Encoding" => ["gzip", "deflate"],
        "Accept" => "application/json"
    ]);
```

### withBody(string $body)

You can expect a certain body on any request by passing a `$body` string to the `withBody()` method.

```php
$this->guzzler->expects($this->once())
    ->withBody("some body string");

// Or, a json based request
    ->withBody(json_encode($someJsonableStructure));
```

### withProtocol($protocol)

You can expect a certain HTTP protocol (1.0, 1.1, 2.0) using the `withProtocol()` method.

```php
$this->guzzler->expects($this->once())
    ->withProtocol(2.0);
```

## Assertions

While `Expectation`s work great for cases where you don’t care about the order of requests to your client, you may find times where you want to verify either the order of requests in your client’s history, or you may want to make assertions about the entirety of its history. Guzzler provides a few convenience assertions for exactly this scenario.

Assertions are also intended to be made after the call to your code under test while `Expectations` are laid out before.

### assertNoHistory(?string $message)

To assert that your code did not make any requests at all, you can use the `assertNoHistory()` method, and pass an optional message argument.

```php
public function testSomething()
{
    // ... 
    
    $this->guzzler->assertNoHistory();
}
```

### assertHistoryCount(int $count, ?string $message)

This method can assert that the client received exactly the specified number of requests, regardless of what the requests were.

```php
public function testSomething()
{
    // ...
    
    $this->guzzler->assertHistoryCount(4);
}
```

### assertFirst(Closure $expect, ?string $message)

Assertions can be made specifically against the first item in the client history. The first argument should be a closure that receives an `Expectation` and an optional error message can be passed as the second argument.

```php
$this->guzzler->assertFirst( function ($expectation) {
    return $expectation->post("/a-url")
        ->withProtocol(1.1)
        ->withHeader("XSRF", "some-string");
});
```

### assertLast(Closure $expect, ?string $message)

Assertions can be made specifically against the last item in the client history. The first argument should be a closure that receives an `Expectation` and an optional error message can be passed as the second argument.

```php
$this->guzzler->assertLast(function ($expectation) {
    return $expectation->get("/some-getter");
});
```

### assertIndex(int $index, Closure $expect, ?string $message)

Assertions can be made against any specific index of the client history. The first argument should be an integer of the index to assert against. The second argument should be a closure that receives an `Expectation` and an optional error message can be passed as the third argument.

```php
$this->guzzler->assertIndex(3, function ($expectation) {
    return $expectation->post("/a-url");
});
```

### assertIndexes(array $indexes, Closure $expect, ?string $message)

Assertions can be made against any specific set of indexes in the client history. The first argument should be an array of integers that correspond to the indexes of history items. The second argument should be a closure that receives an `Expectation` and an optional error message can be passed as the third argument.

```php
$this->guzzler->assertIndexes([2, 3, 6], function ($expectation) {
    return $expectation->withBody("some body string");
});
```

### assertAll(Closure $expect, ?string $message)

This method can be used to assert that every request in the client’s history meets the expectation. For example, you may want to ensure that every request uses a certain authentication header. The first argument should be a closure that receives an `Expectation` and an optional error message as the second argument.

```php
$this->guzzler->assertAll(function (Expectation $ex) use ($authHeader) {
    return $ex->withHeader("Authorization", $authHeader);
});
```

## Helpers

### getHistory()

To retrieve the client’s raw history, this method can be used.

```php
$history = $this->guzzler->getHistory();
```

The shape of Guzzle’s history stack is as follows:

```php
$history = [
    [
        "request"  => GuzzleHttp\Psr7\Request   object
        "response" => GuzzleHttp\Psr7\Response  object
    ],
    // ...
];
```
