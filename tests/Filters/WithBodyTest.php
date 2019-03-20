<?php

namespace tests\Filters;

use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class WithBodyTest extends TestCase
{
    use UsesGuzzler;

    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
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

    public function testWithBodyError()
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\bBody:\b/");
        $this->expectExceptionMessageRegExp("/\bhello\b/");

        $this->guzzler->queueResponse(new Response());
        $this->client->get('/aowei');

        $this->guzzler->assertFirst(function ($e) {
            return $e->withBody('hello');
        });
    }
}