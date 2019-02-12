<?php

namespace Guzzler;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\TestCase;

class Wrapper
{
    protected $history = [];

    protected $handlerStack;

    /** @var MockHandler */
    protected $mockHandler;

    /** @var TestCase */
    protected $testInstance;

    /**
     * @var array [Expectation]
     */
    protected $expectations = [];

    public function __construct(TestCase $testInstance)
    {
        $this->testInstance = $testInstance;

        $this->mockHandler = new MockHandler();
        $this->handlerStack = HandlerStack::create($this->mockHandler);

        $history = Middleware::history($this->history);
        $this->handlerStack->push($history);
    }

    /**
     * Run the cascade of expectations made. This
     * method should be called with an "after"
     * annotation in the Guzzler trait.
     */
    public function runExpectations()
    {
        foreach ($this->expectations as $expectation) {
            $expectation($this->testInstance, $this->history);
        }
    }

    /**
     * Create a client instance with the required handler stacks.
     *
     * @param array $options
     * @return Client
     */
    public function getClient(array $options = []): Client
    {
        return new Client(
            $options + [
                'handler' => $this->handlerStack
            ]
        );
    }

    /**
     * Get the handler stack to pass to a new Client instance.
     *
     * @return HandlerStack
     */
    public function getHandlerStack()
    {
        return $this->handlerStack;
    }

    /**
     *
     */
    public function queueResponse(): void
    {
        foreach (func_get_args() as $response) {
            $this->mockHandler->append($response);
        }
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function expects(Matcher\InvokedRecorder $argument)
    {
        $this->expectations[] = $expectation = new Expectation($argument, $this);

        // Each expectation is an assertion, but because the assertion
        // won't be tested until the @after method, we should add a
        // count for each new expectation that will be asserted.
        $this->testInstance->addToAssertionCount(1);

        return $expectation;
    }
}