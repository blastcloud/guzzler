<?php

namespace tests;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\{Request, Response};
use PHPUnit\Framework\TestCase;
use Guzzler\Guzzler;
use GuzzleHttp\Client;

class GuzzlerTest extends TestCase
{
    /** @var Guzzler */
    public $guzzler;

    public function setUp(): void
    {
        parent::setUp();

        $this->guzzler = new Guzzler($this);
    }

    public function testGetClientReturnsProperInstance()
    {
        $this->assertInstanceOf(Client::class, $this->guzzler->getClient());
    }

    public function testGetClientMergesConfigs()
    {
        $configs = [
            'stream' => true,
            'verify' => false
        ];

        $client = $this->guzzler->getClient($configs);
        $settings = $client->getConfig();

        foreach ($configs as $key => $value) {
            $this->assertEquals($value, $settings[$key]);
        }
    }

    public function testGetHandlerStackReturnsHandlerStack()
    {
        $this->assertInstanceOf(\GuzzleHttp\HandlerStack::class, $this->guzzler->getHandlerStack());
    }

    public function testQueueResponseWithResponse()
    {
        $response = new Response(200, [], 'some special body');
        $this->guzzler->queueResponse($response);

        $result = $this->guzzler->getClient()->get('anything');

        $this->assertEquals($response, $result);
    }

    public function testQueueResponseWithException()
    {
        $exception = new BadResponseException('You suck!', new Request('',''));
        $this->guzzler->queueResponse($exception);

        $this->expectException(BadResponseException::class);

        $this->guzzler->getClient()->get('anything');
    }

    public function testQueueResponseWithPromise()
    {
        $promise = new Promise();
        $this->guzzler->queueResponse($promise);

        $result = $this->guzzler->getClient()->getAsync('somewhere');

        $response = new Response(201, [], 'It was created!');
        $promise->resolve($response);

        $this->assertEquals($response, $result->wait());
    }
}