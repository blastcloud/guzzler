<?php

namespace Guzzler;

use PHPUnit\Framework\TestCase;

class Expectation
{
    protected $wrapper;

    public function __construct($argument, Wrapper $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    public function endpoint(string $uri, string $method)
    {
        // set expectation

        return $this;
    }

    public function withHeader(string $key, $value = null)
    {
        // Set expectation
        return $this;
    }

    public function withHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->withHeader($key, $value);
        }

        return $this;
    }

    public function withBody($body)
    {
        // Set expectation

        return $this;
    }

    public function with($something)
    {
        // Set expectation

        return $this;
    }

    /**
     * Set a follow through; either response, callable, or Exception.
     *
     * @param $response
     * @return $this
     */
    public function will($response)
    {
        $this->wrapper->queueResponse($response);

        return $this;
    }

    /**
     * Iterate over the history and run assertions against it.
     *
     * @param TestCase $instance
     * @param array $history
     */
    public function run(TestCase $instance, array $history): void
    {

    }
}