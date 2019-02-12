<?php

namespace Guzzler;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\TestCase;

class Expectation
{
    /** @var Wrapper */
    protected $wrapper;
    
    /** @var Matcher\InvokedRecorder */
    protected $times;

    /** @var string */
    protected $endpoint;

    /** @var string */
    protected $method;

    protected $headers = [];

    /** @var string */
    protected $body;

    protected $withs = [];

    public function __construct(Matcher\InvokedRecorder $times, Wrapper $wrapper)
    {
        $this->times = $times;
        $this->wrapper = $wrapper;
    }

    public function endpoint(string $uri, string $method)
    {
        $this->endpoint = $uri;
        $this->method = $method;

        return $this;
    }

    public function withHeader(string $key, $value = null)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function withHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->withHeader($key, $value);
        }

        return $this;
    }

    public function withBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    public function with(...$something)
    {
        $this->withs = $something;

        return $this;
    }

    /**
     * Set a follow through; either response, callable, or Exception.
     *
     * @param $response
     * @param int $times
     * @return $this
     */
    public function will($response, int $times = 1)
    {
        for ($i = 0; $i < $times; $i++) {
            $this->wrapper->queueResponse($response);
        }

        return $this;
    }

    /**
     * An alias of 'will'.
     *
     * @param $response
     * @param int $times
     * @return $this
     */
    public function willRespond($response, int $times = 1)
    {
        $this->will($response, $times);

        return $this;
    }

    /**
     * Iterate over the history and run assertions against it.
     *
     * @param TestCase $instance
     * @param array $history
     */
    public function __invoke(TestCase $instance, array $history): void
    {
        $invocations = array_filter($history, function($call) {
            return $call['request']->getMethod() == $this->method
                && $call['request']->getUri() == $this->endpoint;
        });

        foreach ($invocations as $i) {
            $this->times->invoked(new ObjectInvocation('','',[],'',$i));

            /** @var Request $request */
            $request = $i['request'];

            // Headers
            foreach ($this->headers as $key => $value) {
                $message = "Failed asserting that the header {$key} is equal to {$value}.";

                if (is_array($value)) {
                    Assert::assertEquals($value, $request->getHeader($key), $message);
                    continue;
                }

                Assert::assertContains($value, $request->getHeader($key), $message);
            }

            if (!empty($this->body)) {
                Assert::assertEquals($this->body, $request->getBody());
            }
        }

        // Invocation Counts
        $this->times->verify();
    }
}