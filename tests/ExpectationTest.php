<?php

namespace tests;

use GuzzleHttp\Psr7\Response;
use Guzzler\Expectation;
use PHPUnit\Framework\TestCase;

class ExpectationTest extends TestCase
{
    use \Guzzler\UsesGuzzler;

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
        $this->guzzler->queueResponse(new Response(200));

        $this->client->get('/somewhere-else');
    }

    public function testInvocationPassing()
    {
        $this->guzzler->expects($this->once())
            ->endpoint('/once', 'GET');

        $this->guzzler->expects($this->atLeastOnce())
            ->endpoint('/at-least', 'POST');

        $this->guzzler->queueResponse(
            new Response(200),
            new Response(200),
            new Response(200)
        );

        $this->client->get('/once');
        $this->client->post('/at-least');
        $this->client->post('/at-least');
    }

    //public function

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

        $this->client->get('/url', [
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

        $this->client->post('/url', [
            'json' => $body
        ]);
    }

    public function testWithProtocol()
    {
        $this->guzzler->expects($this->once())
            ->withVersion(2.0);

        $this->guzzler->queueResponse(new Response(200));

        $this->client->get('/aoweij', [
            'version' => 2.0
        ]);
    }

    public function testUnknownConvenienceVerb()
    {
        $this->expectException(\Error::class);

        $this->guzzler->expects($this->never())
            ->something('/a-url');
    }

    public function testEachConvenienceVerbMethodDoesntErr()
    {
        $expectation = $this->guzzler->expects($this->never());

        foreach (Expectation::VERBS as $verb) {
            $expectation->$verb('/a-url');
        }
    }
}