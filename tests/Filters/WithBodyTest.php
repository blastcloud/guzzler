<?php

namespace tests\Filters;

use BlastCloud\Guzzler\Expectation;
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

    public function testWithBodyExclusive()
    {
        $body = ['something' => 'some value'];

        $this->guzzler->queueMany(new Response(200), 2);

        $this->client->post('/url', [
            'json' => $body
        ]);

        $this->client->post('/aowe', [
            'body' => json_encode($body) . 'something extra'
        ]);

        $this->guzzler->assertFirst(function (Expectation $e) use ($body) {
            return $e->withBody(json_encode($body), true);
        });

        $this->guzzler->assertNotLast(function (Expectation $e) use ($body) {
            return $e->withBody(json_encode($body), true);
        });
    }

    public function testWithBodyContains()
    {
        $body = 'Some long nasty string to test things against.';

        $this->guzzler->queueResponse(new Response());

        $this->client->post('/awoiue', ['body' => $body]);

        $this->guzzler->assertFirst(function ($e) {
            return $e->withBody('nasty string');
        });
        $this->guzzler->assertNotFirst(function ($e) {
            return $e->withBody('fantastic fantastic');
        });
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