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

    public function testQueueManyWithResponse()
    {
        $response = new Response();
        $this->guzzler->queueMany($response, 12);

        $this->assertEquals(12, $this->guzzler->queueCount());
    }

    public function testQueueCount()
    {
        $this->assertEquals(0, $this->guzzler->queueCount());

        $this->guzzler->queueMany(new Response(), 3);

        $this->assertEquals(3, $this->guzzler->queueCount());
    }

    public function testGetHistory()
    {
        $this->assertEmpty($this->guzzler->getHistory());

        $this->guzzler->queueMany(new Response(), 3);
        $client = $this->guzzler->getClient();
        $client->get('anything');
        $client->post('anything');
        $client->put('anything');

        /**
         * History array is shaped like
         * [
         *      [
         *          'request'  => object,
         *          'response' => object,
         *          'errors'   => array,
         *          'options'  => array
         *      ],
         *      // ...
         * ]
         */
        $this->assertCount(3, $this->guzzler->getHistory());
        $this->assertArrayHasKey('request', $this->guzzler->getHistory(2));
        $this->assertEquals(
            'POST',
            $this->guzzler->getHistory(1, 'request')->getMethod()
        );
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