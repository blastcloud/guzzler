<?php

namespace tests\Filters;

use BlastCloud\Guzzler\Expectation;
use BlastCloud\Guzzler\Filters\WithCallback;
use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class WithCallbackTest extends TestCase
{
    use UsesGuzzler;

    /** @var Client */
    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testWithCallback()
    {
        $this->guzzler->queueResponse(new Response());

        $this->client->get('/woeiue');

        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->withCallback(function ($history) {
                return isset($history['request'])
                    && $history['request'] instanceof Request;
            });
        });

        $this->guzzler->assertNone(function (Expectation $e) {
            return $e->withCallback(function ($history) {
                return false;
            });
        });
    }

    public function testFailureString()
    {
        $this->guzzler->queueResponse(new Response());

        $this->client->get('/aoweiu');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage((new WithCallback())->__toString());

        $this->guzzler->assertAll(function (Expectation $e) {
            return $e->withCallback(function ($history) {
                return false;
            });
        });
    }

    public function testFailureUserString()
    {
        $this->guzzler->queueResponse(new Response());

        $this->client->get('/aowiuew');

        $message = 'My custom callback message.';

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($message);

        $this->guzzler->assertAll(function (Expectation $e) use ($message) {
            return $e->withCallback(function ($history) {
                return false;
            }, $message);
        });
    }
}
