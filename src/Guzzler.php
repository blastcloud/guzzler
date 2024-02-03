<?php

namespace BlastCloud\Guzzler;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\TestCase;
use BlastCloud\Chassis\Chassis;

class Guzzler extends Chassis
{
    /** @var HandlerStack */
    protected $handlerStack;

    /** @var MockHandler */
    protected $mockHandler;

    /** @var Expectation[] */
    protected array $expectations = [];

    public function __construct(TestCase $testInstance)
    {
        parent::__construct($testInstance);

        $this->mockHandler = new MockHandler();
        $this->handlerStack = HandlerStack::create($this->mockHandler);

        $history = Middleware::history($this->history);
        $this->handlerStack->push($history);

        Expectation::addNamespace(__NAMESPACE__.'\\Filters');
    }

    /**
     * Create a client instance with the required handler stacks.
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
     */
    public function getHandlerStack(): HandlerStack
    {
        return $this->handlerStack;
    }

    /**
     * Create a new Expectation instance on which various pieces of the
     * request can be asserted against.
     */
    public function expects(mixed $argument): \BlastCloud\Chassis\Expectation
    {
        return parent::expects($argument);
    }

    protected function createExpectation($argument = null): \BlastCloud\Chassis\Expectation
    {
        return new Expectation($argument, $this);
    }
}