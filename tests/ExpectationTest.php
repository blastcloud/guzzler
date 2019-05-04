<?php

namespace tests;

use GuzzleHttp\Psr7\Response;
use BlastCloud\Guzzler\{Expectation, UsesGuzzler};
use PHPUnit\Framework\TestCase;

class ExpectationTest extends TestCase
{
    use UsesGuzzler;

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

        $this->assertInstanceOf(\BlastCloud\Guzzler\Expectation::class, $result);
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

    public function testInvocationsFails()
    {
        $expectation = (new Expectation($this->once()))
            ->get('/anything');

        try {
            $expectation($this, []);
            $this->fail('Did not throw an invocation fail.');
        } catch (\Exception $e) {
            $this->assertFalse(strstr($e->getMessage(), (string)$expectation) === false);
        }
    }

    public function testWillAndWillRespond()
    {
        $this->guzzler->expects($this->once())
            ->willRespond(new Response());

        $this->client->get('woiej');
    }

    public function testUnknownConvenienceVerb()
    {
        $this->expectException(\Error::class);

        $this->guzzler->expects($this->never())
            ->something('/a-url');
    }

    public function testFailureWhenWithNotFound()
    {
        $this->expectException(\Error::class);
        $class = Expectation::class;
        $this->expectExceptionMessage("Call to undefined method {$class}::withDoesNotExist");

        $this->guzzler->expects($this->never())
            ->withDoesNotExist('something');
    }
}