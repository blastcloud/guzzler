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
            ->endpoint('/once', 'GET');

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

    public function testWithHeaders()
    {
        $headers = [
            'X-Something' => 'Special',
            'host' => 'example.com'
        ];

        $this->guzzler->queueResponse(new Response(200));

        $this->guzzler->expects($this->once())
            ->endpoint('/url', 'GET')
            ->withHeader('Auth', 'Fantastic')
            ->withHeaders($headers);

        $this->guzzler->getClient()->get('/url', [
            'headers' => $headers + ['Auth' => 'Fantastic']
        ]);
    }

    public function testWithBody()
    {
        $body = ['something' => 'some value'];

        $this->guzzler->expects($this->once())
            ->endpoint('/url', 'POST')
            ->withBody(json_encode($body));

        $this->guzzler->queueResponse(new Response(200));

        $this->guzzler->getClient()->post('/url', [
            'json' => $body
        ]);
    }
}