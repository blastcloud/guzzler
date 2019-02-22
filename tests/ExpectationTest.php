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

        $this->client = $this->guzzler->getClient(['base_uri' => 'http://myspecialdomain.com']);
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

        $this->guzzler->queueMany(new Response(), 3);

        $this->client->get('/once');
        $this->client->post('/at-least');
        $this->client->post('/at-least');
    }

    public function testWillAndWillRespond()
    {
        $this->guzzler->expects($this->once())
            ->willRespond(new Response());

        $this->client->get('woiej');
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

    public function testWithOptions()
    {
        $this->guzzler->queueMany(new Response(), 2);

        $this->client->get('/woewij', ['stream' => true]);

        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->withOption('stream', true);
        });

        $options = ['verify' => false, 'allow_redirects' => false];
        $this->guzzler->expects($this->once())
            ->withOptions($options);

        $this->client->get('/woei', $options);
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

    public function testUrlParam()
    {
        $this->guzzler->queueMany(new Response(), 3);

        $this->client->get('/some-url', [
            'query' => ['first' => 'a-value', 'second' => 'another-value']
        ]);

        $this->guzzler->assertFirst(function ($e) {
            return $e->get('/some-url?first=a-value&second=another-value');
        });
    }
}