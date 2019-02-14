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
			‘base_url’ => ‘https://example.com/api’
		]);
		
		// You can then inject this client object into your code or IOC container.
		$this->classToTest = new ClassToTest($client);
	}

	public function testSomethingWithExpectations()
	{
		$this->guzzler->expects($this->once())
			->post(‘/some-url’)
			->withHeader(‘X-Authorization’, ‘some-key’)
			->willRespond(new Response(200));

		$this->classToTest->someMethod();
	}

	public function testSomethingWithAssertions()
	{
		$this->guzzler->queueResponse(
			new Response(204),
			new \Exception(‘Some message’),
			// any needed responses to return from the client.
		);

		$this->classToTest->someMethod();
		// ... Some other number of calls

		$this->guzzler->assertAll(function (Expectation $ex) {
			return $ex->withHeader(‘Authorization’, ‘some-key’);
		});
	}
}
```

## Initializing

The `UsesGuzzler` trait wires up a class variable named `guzzler`. Inside that object the necessary history and mock handlers for Guzzle are instantiated and saved. You can completely customize the `Client` object however you like in two different ways.

### `getClient($options)`

The `getClient` method returns a new instance of the Guzzle `Client` class and adds any options you like to it’s constructor.

```php
$client = $this->guzzler->getClient([
	‘stream’ => true,
	‘base_url’ => ‘http://some-url.com/api/v2’
]);
```

### `getHandlerStack()`

If you’d rather create your own `Client` instance, you can instead get the handler stack required to mock responses from the client.

```php
$client = new Client([
	//... Some configs
	‘handler’ => $this->guzzler->getHandlerStack()
]);
```

## Mocking Responses

There are two main ways to provide responses to return from your client; `queueResponse` directly on the `guzzler` instance, and `will` or `willRespond` on an expectation.

### `queueResponse(...$responses)`

The `queueResponse` method is the main way to 