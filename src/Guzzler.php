<?php

namespace Guzzler;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\{TestCase, MockObject\Matcher\InvokedRecorder};

class Guzzler
{
    use Assertions;

    /** @var HandlerStack  */
    protected $handlerStack;

    /** @var MockHandler */
    protected $mockHandler;

    /** @var array [Expectation] */
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
     * annotation in the UsesGuzzler trait.
     */
    protected function runExpectations()
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
     * Add a response to the mock queue. All responses
     * will return in the order they are given.
     *
     * @param mixed ...$arguments
     */
    public function queueResponse(...$arguments): void
    {
        foreach ($arguments as $response) {
            $this->mockHandler->append($response);
        }
    }

    /**
     * Add a response to the mock queue multiple times.
     *
     * @param mixed $argument
     * @param int $times
     */
    public function queueMany($argument, int $times = 1)
    {
        for ($i = 0; $i < $times; $i++) {
            $this->mockHandler->append($argument);
        }
    }

    /**
     * Get the current count of responses in the mock queue.
     *
     * @return int
     */
    public function queueCount()
    {
        return $this->mockHandler->count();
    }

    /**
     * Return the history stack Guzzle builds with each request/response.
     *
     * @return array
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * Create a new Expectation instance on which various pieces of the
     * request can be asserted against.
     *
     * @param InvokedRecorder $argument
     * @return Expectation
     */
    public function expects(InvokedRecorder $argument)
    {
        $this->expectations[] = $expectation = new Expectation($argument, $this);

        // Each expectation is an assertion, but because the assertion
        // won't be tested until the @after method, we should add a
        // count for each new expectation that will be asserted.
        $this->increment();

        return $expectation;
    }
}