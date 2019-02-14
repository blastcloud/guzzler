<?php

namespace tests;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Guzzler\Wrapper;
use GuzzleHttp\Client;

class WrapperTest extends TestCase
{
    /** @var Wrapper */
    public $wrap;

    public function setUp(): void
    {
        parent::setUp();

        $this->wrap = new Wrapper($this);
    }

    public function testGetClientReturnsProperInstance()
    {
        $this->assertInstanceOf(Client::class, $this->wrap->getClient());
    }

    public function testGetClientMergesConfigs()
    {
        $configs = [
            'stream' => true,
            'verify' => false
        ];

        $client = $this->wrap->getClient($configs);
        $settings = $client->getConfig();

        foreach ($configs as $key => $value) {
            $this->assertEquals($value, $settings[$key]);
        }
    }

    public function testGetHandlerStackReturnsHandlerStack()
    {
        $this->assertInstanceOf(\GuzzleHttp\HandlerStack::class, $this->wrap->getHandlerStack());
    }

    public function testQueueResponseWithResponse()
    {
        $response = new Response(200, [], 'some special body');
        $this->wrap->queueResponse($response);

        $result = $this->wrap->getClient()->get('anything');

        $this->assertEquals($response, $result);
    }

    public function testQueueResponseWithException()
    {
        $exception = new BadResponseException('You suck!', new Request('',''));
        $this->wrap->queueResponse($exception);

        $this->expectException(BadResponseException::class);

        $this->wrap->getClient()->get('anything');
    }

    public function testQueueResponseWithPromise()
    {
        $promise = new Promise();
        $this->wrap->queueResponse($promise);

        $result = $this->wrap->getClient()->getAsync('somewhere');

        $response = new Response(201, [], 'It was created!');
        $promise->resolve($response);

        $this->assertEquals($response, $result->wait());
    }
}