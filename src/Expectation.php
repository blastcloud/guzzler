<?php

namespace Guzzler;

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

    protected $filters = [];

    public function __construct(Matcher\InvokedRecorder $times, Wrapper $wrapper)
    {
        $this->times = $times;
        $this->wrapper = $wrapper;
    }

    protected function addFilter($filter)
    {
        if (!in_array($filter, $this->filters)) {
            $this->filters[] = $filter;
        }
    }

    public function endpoint(string $uri, string $method)
    {
        $this->endpoint = $uri;
        $this->method = $method;

        $this->addFilter('endpoint');

        return $this;
    }

    public function withHeader(string $key, $value = null)
    {
        $this->headers[$key] = $value;

        $this->addFilter('headers');

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

        $this->addFilter('body');

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

    protected function filterByEndpoint(array $history)
    {
        return array_filter($history, function($call) {
            return $call['request']->getMethod() == $this->method
                && $call['request']->getUri() == $this->endpoint;
        });
    }

    protected function filterByHeaders(array $history)
    {
        return array_filter($history, function($call) {
            foreach ($this->headers as $key => $value) {
                $header = $call['request']->getHeader($key);

                if ($header != $value && !in_array($value, $header)) {
                    return false;
                }
            }

            return true;
        });
    }

    protected function filterByBody(array $history) {
        return array_filter($history, function($call) {
            return $call['request']->getBody() == $this->body;
        });
    }

    /**
     * Iterate over the history and verify the invocations against it.
     *
     * @param TestCase $instance
     * @param array $history
     */
    public function __invoke(TestCase $instance, array $history): void
    {
        foreach ($this->filters as $filter) {
            $history = $this->{'filterBy'.ucfirst($filter)}($history);
        }

        foreach ($history as $i) {
            $this->times->invoked(new ObjectInvocation('','',[],'',$i['request']));
        }

        // Invocation Counts
        $this->times->verify();
    }
}