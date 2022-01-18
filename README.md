<p style="text-align:center;"><img src="Guzzler-logo.svg" width="450"></p>

<a href="https://travis-ci.org/blastcloud/guzzler">
    <img src="https://img.shields.io/github/workflow/status/blastcloud/guzzler/run-tests?label=tests">
</a>
<a href="#">
    <img src="https://poser.pugx.org/blastcloud/guzzler/v/stable" />
</a>
<a href="https://packagist.org/packages/blastcloud/guzzler/stats">
    <img src="https://poser.pugx.org/blastcloud/guzzler/downloads" />
</a>
<a href="https://codeclimate.com/github/blastcloud/guzzler/maintainability">
    <img src="https://api.codeclimate.com/v1/badges/01c6f66eaa5db02e5411/maintainability" />
</a>
<a href="https://github.com/blastcloud/guzzler/blob/master/LICENSE.md">
    <img src="https://poser.pugx.org/blastcloud/guzzler/license" />
</a>

---

> Full Documentation at [guzzler.dev](https://guzzler.dev)

Supercharge your app or SDK with a testing library specifically for Guzzle. Guzzler covers the process of setting up a mock handler, recording history of requests, and provides several convenience methods for creating expectations and assertions on that history.

## Installation

```bash
composer require --dev --prefer-dist blastcloud/guzzler
```

## Example Usage

```php
<?php

use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Client;

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

## Documentation

[Full Documentation](https://guzzler.dev)

## License

Guzzler is open-source software licensed under the [MIT License](https://opensource.org/licenses/MIT).
