<?php

namespace Guzzler;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;

class Wrapper
{
    protected $history = [];

    protected $handlerStack;

    /** @var TestCase */
    protected $testInstance;

    /**
     * @var array [Expectation]
     */
    protected $expectations = [];

    public function __construct(TestCase $testInstance)
    {
        $this->testInstance = $testInstance;
        $this->handlerStack = HandlerStack::create();
    }

    /**
     * Run the cascade of expectations made. This
     * method should be called with an "after"
     * annotation in the Guzzler trait.
     */
    public function runExpectations()
    {

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

        }
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function expects($argument)
    {
        $this->expectations[] = $expectation = new Expectation($argument, $this);

        return $expectation;
    }
}