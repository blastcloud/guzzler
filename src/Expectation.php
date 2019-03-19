<?php

namespace BlastCloud\Guzzler;

use BlastCloud\Guzzler\Interfaces\With;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use PHPUnit\Framework\MockObject\Matcher\InvokedRecorder;
use PHPUnit\Framework\TestCase;

/**
 * Class Expectation
 * @package Guzzler
 * @method $this get(string $uri)
 * @method $this post(string $uri)
 * @method $this put(string $uri)
 * @method $this delete(string $uri)
 * @method $this patch(string $uri)
 * @method $this options(string $uri)
 * @method $this withHeader(string $key, $value)
 * @method $this withHeaders(array $values)
 * @method $this withOption(string $key, $value)
 * @method $this withOptions(array $values)
 */
class Expectation
{
    /** @var Guzzler */
    protected $guzzler;

    protected $filters = [];

    /** @var InvokedRecorder */
    protected $times;

    /**
     * Each value in this array becomes a convenience method over endpoint().
     */
    public const VERBS = [
        'get',
        'post',
        'put',
        'delete',
        'patch',
        'options'
    ];

    /**
     * Expectation constructor.
     * @param null|InvokedRecorder $times
     * @param null|Guzzler $guzzler
     */
    public function __construct($times = null, $guzzler = null)
    {
        $this->times = $times;
        $this->guzzler = $guzzler;
    }

    /**
     * @param $name
     * @return bool|With
     */
    protected function isFilter($name)
    {
        $parts = preg_split('/(?=[A-Z])/',$name);
        if ($parts[0] == 'with') {
            return $this->findFilter([$parts[1], rtrim($parts[1], 's')]);
        }

        return false;
    }

    protected function findFilter(array $names) {
        foreach ($names as $name) {
            if (isset($this->filters[$name])) {
                return $this->filters[$name];
            }

            $class = __NAMESPACE__."\\Filters\\With".$name;

            if (class_exists($class)) {
                $this->filters[$name] = $filter = new $class;
                return $filter;
            }
        }

        return false;
    }

    public function endpoint(string $uri, string $method)
    {
        $this->isFilter('withEndpoint')->add('endpoint', [$uri, $method]);

        return $this;
    }

    /**
     * This is used exclusively for the convenience verb methods.
     *
     * @param string $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        // HTTP Verb convenience methods
        if (in_array($name, self::VERBS)) {
            return $this->endpoint($arguments[0], strtoupper($name));
        }

        // Next try to see if it's a with* method we can use.
        if ($filter = $this->isFilter($name)) {
            $filter->add($name, $arguments);
            return $this;
        }

        throw new \Error(sprintf("Call to undefined method %s::%s()", __CLASS__, $name));
    }

    public function synchronous()
    {
        return $this->withOption('synchronous', true);
    }

    public function asynchronous()
    {
        // Set to null, because if the request was asynchronous, the
        // "synchronous" key is not set in the options array.
        return $this->withOption('synchronous', null);
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
            $this->guzzler->queueResponse($response);
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

    protected function runFilters(array $history)
    {
        foreach ($this->filters as $filter) {
            $history = $filter($history);
        }

        return $history;
    }

    /**
     * Iterate over the history and verify the invocations against it.
     *
     * @param TestCase $instance
     * @param array $history
     */
    public function __invoke(TestCase $instance, array $history): void
    {
        foreach ($this->runFilters($history) as $i) {
            $this->times->invoked(new ObjectInvocation('', '', [], '', $i['request']));
        }

        try {
            // Invocation Counts
            $this->times->verify();
        } catch (ExpectationFailedException $e) {
            Assert::fail($e->getMessage() . ' ' . $this->__toString());
        }
    }

    public function __toString()
    {
        $endpoint = $messages = '';

        foreach ($this->filters as $filter) {
            $messages .= $filter->__toString() . "\n";
            if (property_exists($filter, 'endpoint')) {
                $endpoint = $filter->endpoint;
            }
        }

        return <<<MESSAGE


Expectation: {$endpoint}
-----------------------------
{$messages}
MESSAGE;
    }
}