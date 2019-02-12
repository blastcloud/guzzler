<?php

namespace tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ExpectationTest extends TestCase
{
    use \Guzzler\Guzzler;

    /** @var \GuzzleHttp\Client */
    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testExpectsReturnsExpectationInstanceAndIsChainable()
    {
        $result = $this->guzzler->expects($this->never())
            ->endpoint('/somewhere', 'GET');

        $this->assertInstanceOf(\Guzzler\Expectation::class, $result);
    }

    public function testInvocationPassing()
    {
        $this->guzzler->expects($this->once())
            ->endpoint('/once', 'GET')
            ->withHeaders(['Authorization' => 'Blah']);

        $this->guzzler->expects($this->atLeastOnce())
            ->endpoint('/at-least', 'POST');

        $client = $this->guzzler->getClient();

        $this->guzzler->queueResponse(
            new Response(200),
            new Response(200),
            new Response(200)
        );

        $client->get('/once');
        $client->post('/at-least');
        $client->post('/at-least');
    }
}