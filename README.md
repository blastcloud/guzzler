<p align="center"><img src="Guzzler-logo.svg" style="max-width:350px;"></p>

---

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
    
        $client = $this->guzzler->getClient([
            /* Any configs for a client */
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
    
        $this->guzzler->assertAll(function ($expect) {
            return $expect->withHeader("Authorization", "some-key");
        });
    }
}
```

[Full Documentation](https://github.com/blastcloud/guzzler/wiki)